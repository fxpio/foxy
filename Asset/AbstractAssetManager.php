<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Asset;

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
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
     * @var bool
     */
    protected $updatable = true;

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
        return is_dir(self::NODE_MODULES_PATH) && file_exists($this->getPackageName());
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatable($updatable)
    {
        $this->updatable = $updatable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isUpdatable()
    {
        return $this->updatable && $this->isInstalled();
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
            throw new RuntimeException(sprintf('The binary of "%s" must be installed', $this->getName()));
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
        $assetPackage = new AssetPackage($rootPackage, new JsonFile($this->getPackageName()));
        $assetPackage->removeUnusedDependencies($dependencies);
        $alreadyInstalledDependencies = $assetPackage->addNewDependencies($dependencies);

        $this->actionWhenComposerDependenciesAreAlreadyInstalled($alreadyInstalledDependencies);

        return $assetPackage->write();
    }

    /**
     * {@inheritdoc}
     */
    public function run(AssetPackageInterface $assetPackage = null)
    {
        if (true !== $this->config->get('run-asset-manager')) {
            return 0;
        }

        $timeout = ProcessExecutor::getTimeout();
        ProcessExecutor::setTimeout($this->config->get('manager-timeout'));
        $cmd = $this->isUpdatable() ? $this->getUpdateCommand() : $this->getInstallCommand();
        $res = (int) $this->executor->execute($cmd);
        ProcessExecutor::setTimeout($timeout);

        if ($res > 0 && $this->config->get('fallback-asset') && null !== $assetPackage) {
            $assetPackage->restore();
        }

        return $res;
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
