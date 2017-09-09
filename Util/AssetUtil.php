<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Util;

use Composer\Installer\InstallationManager;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Foxy\AssetManager\AssetManagerInterface;
use Foxy\AssetPackage\AssetPackage;

/**
 * Helper for Foxy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetUtil
{
    /**
     * Get the name for the asset dependency.
     *
     * @param PackageInterface $package The package
     *
     * @return string
     */
    public static function getName(PackageInterface $package)
    {
        return AssetPackage::COMPOSER_PREFIX.str_replace(array('/'), '--', $package->getName());
    }

    /**
     * Get the path of asset file.
     *
     * @param InstallationManager   $installationManager The installation manager
     * @param AssetManagerInterface $assetManager        The asset manager
     * @param PackageInterface      $package             The package
     *
     * @return string|null
     */
    public static function getPath(InstallationManager $installationManager, AssetManagerInterface $assetManager, PackageInterface $package)
    {
        $isAsset = static::hasPluginDependency($package->getRequires()) || static::hasPluginDependency($package->getDevRequires());
        $path = null;

        if ($isAsset) {
            $installPath = $installationManager->getInstallPath($package);
            $filename = $installPath.'/'.$assetManager->getPackageName();
            $path = file_exists($filename) ? str_replace('\\', '/', realpath($filename)) : null;
        }

        return $path;
    }

    /**
     * Check if the package contains assets.
     *
     * @param Link[] $requires The require links
     *
     * @return bool
     */
    public static function hasPluginDependency(array $requires)
    {
        $assets = false;

        foreach ($requires as $require) {
            if ('foxy/foxy' === $require->getTarget()) {
                $assets = true;
                break;
            }
        }

        return $assets;
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
    public static function formatPackage(PackageInterface $package, $packageName, array $packageValue)
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
}
