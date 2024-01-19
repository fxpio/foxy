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
use Foxy\Asset\AssetManagerInterface;
use Foxy\Asset\AssetPackage;

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
     * @param array                 $configPackages      The packages defined in config
     *
     * @return null|string
     */
    public static function getPath(InstallationManager $installationManager, AssetManagerInterface $assetManager, PackageInterface $package, array $configPackages = array())
    {
        $path = null;

        if (static::isAsset($package, $configPackages)) {
            $installPath = $installationManager->getInstallPath($package);
            $filename = $installPath.'/'.$assetManager->getPackageName();
            $path = file_exists($filename) ? str_replace('\\', '/', realpath($filename)) : null;
        }

        return $path;
    }

    /**
     * Check if the package is available for Foxy.
     *
     * @param PackageInterface $package        The package
     * @param array            $configPackages The packages defined in config
     *
     * @return bool
     */
    public static function isAsset(PackageInterface $package, array $configPackages = array())
    {
        $projectConfig = self::getProjectActivation($package, $configPackages);
        $enabled = false !== $projectConfig;

        return $enabled && (static::hasExtraActivation($package)
            || static::hasPluginDependency($package->getRequires())
            || static::hasPluginDependency($package->getDevRequires())
            || true === $projectConfig);
    }

    /**
     * Check if foxy is enabled in extra section of package.
     *
     * @param PackageInterface $package The package
     *
     * @return bool
     */
    public static function hasExtraActivation(PackageInterface $package)
    {
        $extra = $package->getExtra();

        return isset($extra['foxy']) && true === $extra['foxy'];
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
     * Check if the package is enabled by the project config.
     *
     * @param PackageInterface $package        The package
     * @param array            $configPackages The packages defined in config
     *
     * @return bool
     */
    public static function isProjectActivation(PackageInterface $package, array $configPackages)
    {
        return true === self::getProjectActivation($package, $configPackages);
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
                $version = $extra['branch-alias'][$version];
            }

            $packageValue['version'] = self::formatVersion(str_replace('-dev', '', $version));
        }

        return $packageValue;
    }

    /**
     * Format the version for the asset package.
     *
     * @param string $version The branch alias version
     *
     * @return string
     */
    private static function formatVersion($version)
    {
        $version = str_replace(array('x', 'X', '*'), '0', $version);
        $exp = explode('.', $version);

        if (($size = \count($exp)) < 3) {
            for ($i = $size; $i < 3; ++$i) {
                $exp[] = '0';
            }
        }

        return $exp[0].'.'.$exp[1].'.'.$exp[2];
    }

    /**
     * Get the activation of the package defined in the project config.
     *
     * @param PackageInterface $package        The package
     * @param array            $configPackages The packages defined in config
     *
     * @return null|bool returns NULL, if the package isn't defined in the project config
     */
    private static function getProjectActivation(PackageInterface $package, array $configPackages)
    {
        $name = $package->getName();
        $value = null;

        foreach ($configPackages as $pattern => $activation) {
            if (\is_int($pattern) && \is_string($activation)) {
                $pattern = $activation;
                $activation = true;
            }

            if ((0 === strpos($pattern, '/') && preg_match($pattern, $name)) || fnmatch($pattern, $name)) {
                $value = $activation;

                break;
            }
        }

        return $value;
    }
}
