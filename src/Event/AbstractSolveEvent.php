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

use Composer\EventDispatcher\Event;
use Composer\Package\PackageInterface;

/**
 * Abstract event for solve event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSolveEvent extends Event
{
    /**
     * @var string
     */
    private $assetDir;

    /**
     * @var PackageInterface[] The composer packages
     */
    private $packages;

    /**
     * Constructor.
     *
     * @param string             $name     The event name
     * @param string             $assetDir The directory of mock assets
     * @param PackageInterface[] $packages All installed Composer packages
     */
    public function __construct($name, $assetDir, array $packages)
    {
        parent::__construct($name, array(), array());

        $this->assetDir = $assetDir;
        $this->packages = $packages;
    }

    /**
     * Get the directory of mock assets.
     *
     * @return string
     */
    public function getAssetDir()
    {
        return $this->assetDir;
    }

    /**
     * Get the installed Composer packages.
     *
     * @return PackageInterface[]
     */
    public function getPackages()
    {
        return $this->packages;
    }
}
