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

use Composer\Package\AliasPackage;
use Composer\Package\Loader\ArrayLoader;

/**
 * Helper for package.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PackageUtil
{
    /**
     * Load all packages in the lock data of locker.
     *
     * @param array $lockData The lock data of locker
     *
     * @return array The lock data
     */
    public static function loadLockPackages(array $lockData)
    {
        $loader = new ArrayLoader();
        $lockData = static::loadLockPackage($loader, $lockData);
        $lockData = static::loadLockPackage($loader, $lockData, true);
        $lockData = static::convertLockAlias($lockData);

        return $lockData;
    }

    /**
     * Load the packages in the packages section of the locker load data.
     *
     * @param ArrayLoader $loader   The package loader
     * @param array       $lockData The lock data of locker
     * @param bool        $dev      Check if the dev packages must be loaded
     *
     * @return array The lock data
     */
    public static function loadLockPackage(ArrayLoader $loader, array $lockData, $dev = false)
    {
        $key = $dev ? 'packages-dev' : 'packages';

        if (isset($lockData[$key])) {
            foreach ($lockData[$key] as $i => $package) {
                $package = $loader->load($package);
                $lockData[$key][$i] = $package instanceof AliasPackage ? $package->getAliasOf() : $package;
            }
        }

        return $lockData;
    }

    /**
     * Convert the package aliases of the locker load data.
     *
     * @param array $lockData The lock data of locker
     *
     * @return array The lock data
     */
    public static function convertLockAlias(array $lockData)
    {
        if (isset($lockData['aliases'])) {
            $aliases = array();

            foreach ($lockData['aliases'] as $i => $config) {
                $aliases[$config['package']][$config['version']] = array(
                    'alias' => $config['alias'],
                    'alias_normalized' => $config['alias_normalized'],
                );
            }

            $lockData['aliases'] = $aliases;
        }

        return $lockData;
    }
}
