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
use Foxy\Config\Config;
use Foxy\Exception\RuntimeException;

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
    public function isInstalled()
    {
        return is_dir('./node_modules');
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
        $packageContent = $this->injectDependencies($rootPackage, $dependencies);
        $res = 0;

        if ($this->config->get('run-asset-manager')) {
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
     * @param RootPackageInterface $rootPackage  The composer root package
     * @param array                $dependencies The asset local dependencies
     *
     * @return string|null
     */
    protected function injectDependencies(RootPackageInterface $rootPackage, array $dependencies)
    {
        $jsonFile = new JsonFile($this->getPackageName());
        $package = $this->getAssetPackage($jsonFile, $rootPackage);
        $installedAssets = $this->getInstalledDependencies($package);
        $package = $this->removeUnusedDependencies($package, $dependencies, $installedAssets);
        $package = $this->addNewDependencies($package, $dependencies);

        $content = isset($package['_content']) && is_string($package['_content']) ? $package['_content'] : null;
        unset($package['_content']);
        $jsonFile->write($package);

        return $content;
    }

    /**
     * Get the asset package with the content of json file in '_content' section.
     *
     * @param JsonFile             $jsonFile    The json file
     * @param RootPackageInterface $rootPackage The composer root package
     *
     * @return array
     */
    protected function getAssetPackage(JsonFile $jsonFile, RootPackageInterface $rootPackage)
    {
        if ($jsonFile->exists()) {
            $content = file_get_contents($this->getPackageName());
            $package = (array) $jsonFile->read();
        } else {
            $content = null;
            $package = array();
        }

        $package['_content'] = $content;

        return $this->injectRequiredKeys($rootPackage, $package);
    }

    /**
     * Get the installed asset dependencies.
     *
     * @param array $package The asset package
     *
     * @return array The installed asset dependencies
     */
    protected function getInstalledDependencies(array $package)
    {
        $section = $this->getSectionDependencies();
        $installedAssets = array();

        if (isset($package[$section]) && is_array($package[$section])) {
            foreach ($package[$section] as $dependency => $version) {
                if (0 === strpos($dependency, '@composer-asset/')) {
                    $installedAssets[$dependency] = $version;
                }
            }
        }

        return $installedAssets;
    }

    /**
     * Remove the unused asset dependencies.
     *
     * @param array $package         The asset package
     * @param array $dependencies    The asset dependencies
     * @param array $installedAssets The installed asset dependencies
     *
     * @return array The asset package
     */
    protected function removeUnusedDependencies(array $package, array $dependencies, array $installedAssets)
    {
        $section = $this->getSectionDependencies();
        $removeDependencies = array_diff_key($installedAssets, $dependencies);

        foreach ($removeDependencies as $dependency => $version) {
            unset($package[$section][$dependency]);
        }

        return $package;
    }

    /**
     * Add the new asset dependencies.
     *
     * @param array $package      The asset package
     * @param array $dependencies The asset dependencies
     *
     * @return array The asset package
     */
    protected function addNewDependencies(array $package, array $dependencies)
    {
        $section = $this->getSectionDependencies();

        foreach ($dependencies as $name => $path) {
            if (!isset($installedAssets[$name])) {
                $package[$section][$name] = 'file:./'.dirname($path);
            } else {
                $this->actionWhenComposerDependencyIsAlreadyInstalled($name);
            }
        }

        return $package;
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
     * @param string|null $packageContent The backup content of package file
     *
     * @return int
     */
    protected function runAssetManager($packageContent)
    {
        $timeout = ProcessExecutor::getTimeout();
        ProcessExecutor::setTimeout(null);
        $cmd = $this->isInstalled() ? $this->getUpdateCommand() : $this->getInstallCommand();
        $res = $this->executor->execute($cmd);
        ProcessExecutor::setTimeout($timeout);

        if ($res && $this->config->get('fallback-asset')) {
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

    /**
     * Inject the required keys for asset package defined in root composer package.
     *
     * @param RootPackageInterface $rootPackage The composer root package
     * @param array                $package     The asset package
     *
     * @return array The asset package
     */
    protected function injectRequiredKeys(RootPackageInterface $rootPackage, array $package)
    {
        if (!isset($package['license']) && count($rootPackage->getLicense()) > 0 && !isset($package['private'])) {
            $license = current($rootPackage->getLicense());

            if ('proprietary' === $license) {
                $package['private'] = true;
            } else {
                $package['license'] = $license;
            }
        }

        return $package;
    }
}
