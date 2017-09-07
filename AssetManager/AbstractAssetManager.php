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
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Foxy\Config\Config;
use Foxy\Exception\RuntimeException;
use Foxy\Foxy;

/**
 * Abstract Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractAssetManager implements AssetManagerInterface
{
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
    public function getSectionDependencies()
    {
        return 'dependencies';
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
    public function validate()
    {
        $this->executor->execute($this->getVersionCommand(), $version);
        $version = trim($version);
        $constraintVersion = $this->config->get('manager-version', Foxy::DEFAULT_CONFIG['manager-version']);

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
    public function addDependencies(array $dependencies)
    {
        $packageContent = $this->injectDependencies($dependencies);
        $res = 0;

        if ($this->config->get('run-asset-manager', Foxy::DEFAULT_CONFIG['run-asset-manager'])) {
            $res = $this->runAssetManager($packageContent);
        }

        return $res;
    }

    /**
     * Get the command to retrieve the version.
     *
     * @return string
     */
    abstract public function getVersionCommand();

    /**
     * Get the command to install the asset dependencies.
     *
     * @return string
     */
    abstract public function getInstallCommand();

    /**
     * Get the command to update the asset dependencies.
     *
     * @return string
     */
    abstract public function getUpdateCommand();

    /**
     * Add the asset dependencies in asset package file and retrieve the backup content of package file.
     *
     * @param array $dependencies The asset local dependencies
     *
     * @return string|null
     */
    protected function injectDependencies(array $dependencies)
    {
        $jsonFile = new JsonFile($this->getPackageName());
        $section = $this->getSectionDependencies();
        $installedAssets = array();

        if ($jsonFile->exists()) {
            $content = file_get_contents($this->getPackageName());
            $package = (array) $jsonFile->read();
        } else {
            $content = null;
            $package = array();
        }

        if (isset($package[$section]) && is_array($package[$section])) {
            foreach ($package[$section] as $dependency => $version) {
                if (0 === strpos($dependency, '@composer-asset/')) {
                    $installedAssets[$dependency] = $version;
                }
            }
        }

        $removeDependencies = array_diff_key($installedAssets, $dependencies);
        foreach ($removeDependencies as $dependency => $version) {
            unset($package[$section][$dependency]);
        }

        foreach ($dependencies as $name => $path) {
            if (!isset($installedAssets[$name])) {
                $package[$section][$name] = 'file:./'.dirname($path);
            } else {
                $this->actionWhenComposerDependencyIsAlreadyInstalled($name);
            }
        }

        $jsonFile->write($package);

        return $content;
    }

    /**
     * Action when the composer dependency is already installed.
     *
     * @param string $name the asset package name of composer dependency
     */
    protected function actionWhenComposerDependencyIsAlreadyInstalled($name)
    {
        // do nothing by default
    }

    /**
     * Run the asset manager to install/update the asset dependencies.
     *
     * @param string|null The backup content of package file
     *
     * @return int
     */
    protected function runAssetManager($packageContent)
    {
        $timeout = ProcessExecutor::getTimeout();
        ProcessExecutor::setTimeout(null);
        $cmd = $this->hasLockFile() ? $this->getUpdateCommand() : $this->getInstallCommand();
        $res = $this->executor->execute($cmd);
        ProcessExecutor::setTimeout($timeout);

        if ($res && $this->config->get('fallback-asset', Foxy::DEFAULT_CONFIG['fallback-asset'])) {
            $this->fs->remove($this->getPackageName());

            if (null !== $packageContent) {
                file_put_contents($this->getPackageName(), $packageContent);
            }
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
}
