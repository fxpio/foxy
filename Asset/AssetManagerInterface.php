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

use Composer\Package\RootPackageInterface;
use Foxy\Exception\RuntimeException;
use Foxy\Fallback\FallbackInterface;

/**
 * Interface of asset manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface AssetManagerInterface
{
    /**
     * Get the name of asset manager.
     *
     * @return string
     */
    public function getName();

    /**
     * Check if the asset manager is available.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Get the filename of the asset package.
     *
     * @return string
     */
    public function getPackageName();

    /**
     * Check if the lock file is present or not.
     *
     * @return bool
     */
    public function hasLockFile();

    /**
     * Check if the asset dependencies are installed or not.
     *
     * @return bool
     */
    public function isInstalled();

    /**
     * Set the fallback.
     *
     * @param FallbackInterface $fallback The fallback
     *
     * @return self
     */
    public function setFallback(FallbackInterface $fallback);

    /**
     * Define if the asset manager can be use the update command.
     *
     * @param bool $updatable The value
     *
     * @return self
     */
    public function setUpdatable($updatable);

    /**
     * Check if the asset manager can be use the update command or not.
     *
     * @return bool
     */
    public function isUpdatable();

    /**
     * Check if the asset package is valid for the update.
     *
     * @return bool
     */
    public function isValidForUpdate();

    /**
     * Get the filename of the lock file.
     *
     * @return string
     */
    public function getLockPackageName();

    /**
     * Validate the version of asset manager.
     *
     * @throws RuntimeException When the binary isn't installed
     * @throws RuntimeException When the version doesn't match
     */
    public function validate();

    /**
     * Add the asset dependencies in asset package file.
     *
     * @param RootPackageInterface $rootPackage  The composer root package
     * @param array                $dependencies The asset local dependencies
     *
     * @return AssetPackageInterface
     */
    public function addDependencies(RootPackageInterface $rootPackage, array $dependencies);

    /**
     * Run the asset manager to install/update the asset dependencies.
     *
     * @return int
     */
    public function run();
}
