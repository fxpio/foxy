<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Solver;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Foxy\AssetManager\AssetManagerInterface;
use Foxy\Config\Config;
use Foxy\Exception\RuntimeException;
use Foxy\Util\AssetUtil;

/**
 * Solver of asset dependencies.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Solver implements SolverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var AssetManagerInterface
     */
    protected $assetManager;

    /**
     * Constructor.
     *
     * @param AssetManagerInterface $assetManager The asset manager
     * @param Config                $config       The config
     * @param Filesystem            $filesystem   The composer filesystem
     */
    public function __construct(AssetManagerInterface $assetManager, Config $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->fs = $filesystem;
        $this->assetManager = $assetManager;
    }

    /**
     * {@inheritdoc}
     */
    public function solve(Composer $composer, IOInterface $io)
    {
        if (!$this->config->get('enabled')) {
            return;
        }

        $info = sprintf('<info>%s %s dependencies</info>', $this->assetManager->isInstalled() ? 'Updating' : 'Installing', $this->assetManager->getName());
        $io->write($info);

        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $assetDir = $this->config->get('composer-asset-dir', $vendorDir.'/foxy/composer-asset/');
        $this->fs->remove($assetDir);

        $assets = $this->getAssets($composer, $assetDir, $packages);
        $res = $this->assetManager->addDependencies($composer->getPackage(), $assets);

        if ($res && $this->config->get('fallback-composer')) {
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
                list($packageName, $packagePath) = $this->getMockPackagePath($package, $assetDir, $filename);
                $assets[$packageName] = $packagePath;
            }
        }

        return $assets;
    }

    /**
     * Get the path of the mock package.
     *
     * @param PackageInterface $package  The package dependency
     * @param string           $assetDir The asset directory
     * @param string           $filename The filename of asset package
     *
     * @return string[] The package name and the relative package path from the current directory
     */
    protected function getMockPackagePath(PackageInterface $package, $assetDir, $filename)
    {
        $packageName = AssetUtil::getName($package);
        $packagePath = $assetDir.$package->getName();
        $newFilename = $packagePath.'/'.basename($filename);
        mkdir($packagePath, 0777, true);
        copy($filename, $newFilename);

        $jsonFile = new JsonFile($newFilename);
        $packageValue = AssetUtil::formatPackage($package, $packageName, (array) $jsonFile->read());

        $jsonFile->write($packageValue);

        return array($packageName, $this->fs->findShortestPath(getcwd(), $newFilename));
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
