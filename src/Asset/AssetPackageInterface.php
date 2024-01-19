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

/**
 * Interface of asset package.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface AssetPackageInterface
{
    /**
     * Write the asset package in file.
     *
     * @return self
     */
    public function write();

    /**
     * Set the asset package.
     *
     * @param array $package The asset package
     *
     * @return self
     */
    public function setPackage(array $package);

    /**
     * Get the asset package.
     *
     * @return array
     */
    public function getPackage();

    /**
     * Get the installed asset dependencies.
     *
     * @return array The installed asset dependencies
     */
    public function getInstalledDependencies();

    /**
     * Add the new asset dependencies and return the names of already installed asset dependencies.
     *
     * @param array $dependencies The asset dependencies
     *
     * @return array The asset package name of the already asset dependencies
     */
    public function addNewDependencies(array $dependencies);

    /**
     * Remove the unused asset dependencies.
     *
     * @param array $dependencies All asset dependencies
     *
     * @return self
     */
    public function removeUnusedDependencies(array $dependencies);
}
