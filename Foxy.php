<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Foxy\AssetManager\AssetManagerInterface;
use Foxy\Config\Config;
use Foxy\Config\ConfigBuilder;
use Foxy\Exception\RuntimeException;
use Foxy\Util\AssetUtil;

/**
 * Composer plugin.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Foxy implements PluginInterface, EventSubscriberInterface
{
    /**
     * The list of the classes of asset managers.
     */
    const ASSET_MANAGERS = array(
        'Foxy\AssetManager\NpmManager',
        'Foxy\AssetManager\YarnManager',
    );

    /**
     * The default values of config.
     */
    const DEFAULT_CONFIG = array(
        'enabled' => true,
        'manager' => 'npm',
        'manager-version' => null,
        'manager-bin' => null,
        'manager-install-options' => null,
        'manager-update-options' => null,
        'composer-asset-dir' => null,
        'run-asset-manager' => true,
        'fallback-asset' => true,
        'fallback-composer' => true,
    );

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
     * @var AssetManagerInterface[]
     */
    protected $assetManagers = array();

    /**
     * @var AssetManagerInterface
     */
    protected $assetManager;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => array(
                array('solveAssets', 100),
            ),
            ScriptEvents::POST_UPDATE_CMD => array(
                array('solveAssets', 100),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->config = ConfigBuilder::build($composer, $io);
        $this->executor = new ProcessExecutor($io);
        $this->fs = new Filesystem($this->executor);
        $manager = $this->config->get('manager', static::DEFAULT_CONFIG['manager']);

        foreach (static::ASSET_MANAGERS as $class) {
            $this->addAssetManager(new $class($this->config, $this->executor, $this->fs));
        }

        if (!isset($this->assetManagers[$manager])) {
            throw new RuntimeException(sprintf('The asset manager "%s" doesn\'t exist', $manager));
        }

        $this->assetManager = $this->assetManagers[$manager];
        $this->assetManager->validate();
    }

    /**
     * Solve the assets.
     *
     * @param Event $event The composer script event
     */
    public function solveAssets(Event $event)
    {
        if (!$this->config->get('enabled', static::DEFAULT_CONFIG['enabled'])) {
            return;
        }

        $io = $event->getIO();
        $composer = $event->getComposer();
        $info = sprintf('<info>%s %s dependencies</info>', $this->assetManager->hasLockFile() ? 'Updating' : 'Installing', $this->assetManager->getName());
        $io->write($info);

        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $assetDir = $this->config->get('composer-asset-dir', $vendorDir.'/foxy/composer-asset/');
        $this->fs->remove($assetDir);

        $assets = $this->getAssets($composer, $assetDir, $packages);
        $res = $this->assetManager->addDependencies($assets);

        if ($res && $this->config->get('fallback-composer', static::DEFAULT_CONFIG['fallback-composer'])) {
            $this->fallbackComposerLockFile($composer, $io);
        }
    }

    /**
     * Get the package of asset dependencies.
     *
     * @param Composer           $composer The composer
     * @param string             $assetDir The asset directory
     * @param PackageInterface[] $packages The package dependencies
     *
     * @return array[]
     */
    protected function getAssets(Composer $composer, $assetDir, array $packages)
    {
        $installationManager = $composer->getInstallationManager();
        $assets = array();

        foreach ($packages as $package) {
            $filename = AssetUtil::getPath($installationManager, $this->assetManager, $package);

            if (null !== $filename) {
                $packageName = AssetUtil::getName($package);
                $packagePath = $assetDir.$package->getName();
                $newFilename = $packagePath.'/'.basename($filename);
                mkdir($packagePath, 0777, true);
                copy($filename, $newFilename);

                $jsonFile = new JsonFile($newFilename);
                $packageValue = $this->formatPackage($package, $packageName, (array) $jsonFile->read());
                $jsonFile->write($packageValue);
                $assets[$packageName] = $this->fs->findShortestPath(getcwd(), $newFilename);
            }
        }

        return $assets;
    }

    /**
     * Add the asset manager.
     *
     * @param AssetManagerInterface $assetManager The asset manager
     *
     * @return $this
     */
    protected function addAssetManager(AssetManagerInterface $assetManager)
    {
        $this->assetManagers[$assetManager->getName()] = $assetManager;

        return $this;
    }

    /**
     * Format the asset package.
     *
     * @param PackageInterface $package      The composer package
     * @param string           $packageName  The package name
     * @param array            $packageValue The package value
     *
     * @return array
     */
    protected function formatPackage(PackageInterface $package, $packageName, array $packageValue)
    {
        $packageValue['name'] = $packageName;

        if (!isset($packageValue['version'])) {
            $extra = $package->getExtra();
            $version = $package->getPrettyVersion();

            if (0 === strpos($version, 'dev-') && isset($extra['branch-alias'][$version])) {
                $version = str_replace('-dev', '', $extra['branch-alias'][$version]);
                $exp = explode('.', $version);

                if (count($exp) < 3) {
                    $exp[] = '0';
                }

                $version = implode('.', $exp);
            }

            $packageValue['version'] = $version;
        }

        return $packageValue;
    }

    /**
     * Fallback the composer lock file and dependencies.
     *
     * @param Composer    $composer The composer
     * @param IOInterface $io       The io of composer
     */
    protected function fallbackComposerLockFile(Composer $composer, IOInterface $io)
    {
        throw new RuntimeException('The fallback for the Composer lock file and its dependencies is not implemented currently');
    }
}
