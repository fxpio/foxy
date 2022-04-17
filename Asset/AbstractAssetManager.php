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

use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Semver\VersionParser;
use Composer\Util\Filesystem;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use Foxy\Config\Config;
use Foxy\Converter\SemverConverter;
use Foxy\Converter\VersionConverterInterface;
use Foxy\Exception\RuntimeException;
use Foxy\Fallback\FallbackInterface;
use Foxy\Json\JsonFile;

/**
 * Abstract Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractAssetManager implements AssetManagerInterface
{
    const NODE_MODULES_PATH = './node_modules';

    /**
     * @var IOInterface
     */
    protected $io;

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
     * @var VersionConverterInterface
     */
    protected $versionConverter;

    /**
     * @var null|FallbackInterface
     */
    protected $fallback;

    /**
     * @var bool
     */
    protected $updatable = true;

    /**
     * @var null|string
     */
    private $version = '';

    /**
     * Constructor.
     *
     * @param IOInterface                    $io               The IO
     * @param Config                         $config           The config
     * @param ProcessExecutor                $executor         The process
     * @param Filesystem                     $fs               The filesystem
     * @param null|FallbackInterface         $fallback         The asset fallback
     * @param null|VersionConverterInterface $versionConverter The version converter
     */
    public function __construct(
        IOInterface $io,
        Config $config,
        ProcessExecutor $executor,
        Filesystem $fs,
        FallbackInterface $fallback = null,
        VersionConverterInterface $versionConverter = null
    ) {
        $this->io = $io;
        $this->config = $config;
        $this->executor = $executor;
        $this->fs = $fs;
        $this->fallback = $fallback;
        $this->versionConverter = null !== $versionConverter ? $versionConverter : new SemverConverter();
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return null !== $this->getVersion();
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
    public function setFallback(FallbackInterface $fallback)
    {
        $this->fallback = $fallback;

        return $this;
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
        return $this->updatable && $this->isInstalled() && $this->isValidForUpdate();
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForUpdate()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $version = $this->getVersion();
        $constraintVersion = $this->config->get('manager-version');

        if (null === $version) {
            throw new RuntimeException(sprintf('The binary of "%s" must be installed', $this->getName()));
        }

        if ($constraintVersion) {
            $parser = new VersionParser();
            $constraint = $parser->parseConstraints($constraintVersion);

            if (!$constraint->matches($parser->parseConstraints($version))) {
                throw new RuntimeException(sprintf('The installed %s version "%s" doesn\'t match with the constraint version "%s"', $this->getName(), $version, $constraintVersion));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDependencies(RootPackageInterface $rootPackage, array $dependencies)
    {
        $assetPackage = new AssetPackage($rootPackage, new JsonFile($this->getPackageName(), null, $this->io));
        $assetPackage->removeUnusedDependencies($dependencies);
        $alreadyInstalledDependencies = $assetPackage->addNewDependencies($dependencies);

        $this->actionWhenComposerDependenciesAreAlreadyInstalled($alreadyInstalledDependencies);
        $this->io->write('<info>Merging Composer dependencies in the asset package</info>');

        return $assetPackage->write();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (true !== $this->config->get('run-asset-manager')) {
            return 0;
        }

        $updatable = $this->isUpdatable();
        $info = sprintf('<info>%s %s dependencies</info>', $updatable ? 'Updating' : 'Installing', $this->getName());
        $this->io->write($info);

        $timeout = ProcessExecutor::getTimeout();
        ProcessExecutor::setTimeout($this->config->get('manager-timeout', PHP_INT_MAX));
        $cmd = $updatable ? $this->getUpdateCommand() : $this->getInstallCommand();
        $res = (int) $this->executor->execute($cmd);
        ProcessExecutor::setTimeout($timeout);

        if ($res > 0 && null !== $this->fallback) {
            $this->fallback->restore();
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
     * @param string          $defaultBin The default binary of command if option isn't defined
     * @param string          $action     The command action to retrieve the options in config
     * @param string|string[] $command    The command
     *
     * @return string
     */
    protected function buildCommand($defaultBin, $action, $command)
    {
        $bin = $this->config->get('manager-bin', $defaultBin);
        $bin = Platform::isWindows() ? str_replace('/', '\\', $bin) : $bin;
        $gOptions = trim($this->config->get('manager-options', ''));
        $options = trim($this->config->get('manager-'.$action.'-options', ''));

        return $bin.' '.implode(' ', (array) $command)
            .(empty($gOptions) ? '' : ' '.$gOptions)
            .(empty($options) ? '' : ' '.$options);
    }

    /**
     * @return null|string
     */
    protected function getVersion()
    {
        if ('' === $this->version) {
            $this->executor->execute($this->getVersionCommand(), $version);
            $this->version = '' !== trim($version) ? $this->versionConverter->convertVersion(trim($version)) : null;
        }

        return $this->version;
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
