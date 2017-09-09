<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\AssetManager;

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Foxy\AssetPackage\AssetPackage;
use Foxy\AssetPackage\AssetPackageInterface;
use Foxy\Config\Config;
use Foxy\Exception\RuntimeException;

/**
 * Abstract Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractAssetManager implements AssetManagerInterface
{
    const NODE_MODULES_PATH = './node_modules';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProcessExecutor
     */
    protected $executor;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * Constructor.
     *
     * @param Config          $config   The config
     * @param ProcessExecutor $executor The process
     * @param Filesystem      $fs       The filesystem
     */
    public function __construct(Config $config, ProcessExecutor $executor, Filesystem $fs)
    {
        $this->config = $config;
        $this->executor = $executor;
        $this->fs = $fs;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageName()
    {
        return 'package.json';
    }

    /**
     * {@inheritdoc}
     */
    public function hasLockFile()
    {
        return file_exists($this->getLockPackageName());
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return is_dir(self::NODE_MODULES_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $this->executor->execute($this->getVersionCommand(), $version);
        $version = trim($version);
        $constraintVersion = $this->config->get('manager-version');

        if ('' === $version) {
            throw new \RuntimeException(sprintf('The binary of "%s" must be installed', $this->getName()));
        }

        if ($constraintVersion) {
            $parser = new VersionParser();
            $constraint = $parser->parseConstraints($constraintVersion);

            if (!$constraint->matches(new Constraint('=', $version))) {
                throw new RuntimeException(sprintf('The installed %s version "%s" doesn\'t match with the constraint version "%s"', $this->getName(), $version, $constraintVersion));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDependencies(RootPackageInterface $rootPackage, array $dependencies)
    {
        $assetPackage = $this->injectDependencies($rootPackage, $dependencies);
        $res = 0;

        if ($this->config->get('run-asset-manager')) {
            $res = $this->runAssetManager($assetPackage);
        }

        return $res;
    }

    /**
     * Add the asset dependencies in asset package file and retrieve the backup content of package file.
     *
     * @param RootPackageInterface $rootPackage  The composer root package
     * @param array                $dependencies The asset local dependencies
     *
     * @return AssetPackageInterface
     */
    protected function injectDependencies(RootPackageInterface $rootPackage, array $dependencies)
    {
        $assetPackage = new AssetPackage($rootPackage, new JsonFile($this->getPackageName()), $this->fs);
        $assetPackage->removeUnusedDependencies($dependencies);
        $alreadyInstalledDependencies = $assetPackage->addNewDependencies($dependencies);

        $this->actionWhenComposerDependenciesAreAlreadyInstalled($alreadyInstalledDependencies);

        return $assetPackage->write();
    }

    /**
     * Action when the composer dependencies are already installed.
     *
     * @param string[] $names the asset package name of composer dependencies
     */
    protected function actionWhenComposerDependenciesAreAlreadyInstalled($names)
    {
        // do nothing by default
    }

    /**
     * Run the asset manager to install/update the asset dependencies.
     *
     * @param AssetPackageInterface $assetPackage The asset package
     *
     * @return int
     */
    protected function runAssetManager(AssetPackageInterface $assetPackage)
    {
        $timeout = ProcessExecutor::getTimeout();
        ProcessExecutor::setTimeout(null);
        $cmd = $this->isInstalled() ? $this->getUpdateCommand() : $this->getInstallCommand();
        $res = (int) $this->executor->execute($cmd);
        ProcessExecutor::setTimeout($timeout);

        if ($res > 0 && $this->config->get('fallback-asset')) {
            $assetPackage->restore();
        }

        return $res;
    }

    /**
     * Build the command with binary and command options.
     *
     * @param string $defaultBin The default binary of command if option isn't defined
     * @param string $action     The command action to retrieve the options in config
     * @param string $command    The command
     *
     * @return string
     */
    protected function buildCommand($defaultBin, $action, $command)
    {
        $bin = $this->config->get('manager-bin', $defaultBin);
        $options = trim($this->config->get('manager-'.$action.'-options', ''));

        return $bin.' '.$command.(empty($options) ? '' : ' '.$options);
    }

    /**
     * Get the command to retrieve the version.
     *
     * @return string
     */
    abstract protected function getVersionCommand();

    /**
     * Get the command to install the asset dependencies.
     *
     * @return string
     */
    abstract protected function getInstallCommand();

    /**
     * Get the command to update the asset dependencies.
     *
     * @return string
     */
    abstract protected function getUpdateCommand();
}
