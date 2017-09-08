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

use Composer\Package\RootPackageInterface;
use Foxy\Exception\RuntimeException;

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
     * Get the filename of the asset package.
     *
     * @return string
     */
    public function getPackageName();

    /**
     * Get the section name of dependencies.
     *
     * @return string
     */
    public function getSectionDependencies();

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
     * @return int
     */
    public function addDependencies(RootPackageInterface $rootPackage, array $dependencies);
}
