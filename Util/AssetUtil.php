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
        return '@composer-asset/'.str_replace(array('/'), '--', $package->getName());
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
        $path = $installationManager->getInstallPath($package);
        $filename = $path.'/'.$assetManager->getPackageName();

        return $isAsset && file_exists($filename) ? str_replace('\\', '/', realpath($filename)) : null;
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
}
