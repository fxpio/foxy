<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Event;

use Composer\Package\PackageInterface;
use Foxy\FoxyEvents;

/**
 * Get assets event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GetAssetsEvent extends AbstractSolveEvent
{
    /**
     * @var array
     */
    private $assets;

    /**
     * Constructor.
     *
     * @param string             $assetDir The directory of mock assets
     * @param PackageInterface[] $packages All installed Composer packages
     * @param array              $assets   The map of asset package name and the asset package path
     */
    public function __construct($assetDir, array $packages, array $assets)
    {
        parent::__construct(FoxyEvents::GET_ASSETS, $assetDir, $packages);

        $this->assets = $assets;
    }

    /**
     * Check if the asset package is present.
     *
     * @param string $name The asset package name
     *
     * @return bool
     */
    public function hasAsset($name)
    {
        return isset($this->assets[$name]);
    }

    /**
     * Add the asset package.
     *
     * @param string $name The asset package name
     * @param string $path The asset package path (relative path form root project
     *                     and started with `file:`)
     *
     * Example:
     *
     * For the Composer package `foo/bar`.
     *
     * $event->addAsset('@composer-asset/foo--bar',
     *                  'file:./vendor/foxy/composer-asset/foo/bar');
     *
     * @return self
     */
    public function addAsset($name, $path)
    {
        $this->assets[$name] = $path;

        return $this;
    }

    /**
     * Get the map of asset package name and the asset package path.
     *
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }
}
