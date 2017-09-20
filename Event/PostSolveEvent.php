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
 * Post solve event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PostSolveEvent extends AbstractSolveEvent
{
    /**
     * @var int
     */
    private $runResult;

    /**
     * Constructor.
     *
     * @param string             $assetDir  The directory of mock assets
     * @param PackageInterface[] $packages  All installed Composer packages
     * @param int                $runResult The process result of asset manager execution
     */
    public function __construct($assetDir, array $packages, $runResult)
    {
        parent::__construct(FoxyEvents::POST_SOLVE, $assetDir, $packages);

        $this->runResult = $runResult;
    }

    /**
     * Get the process result of asset manager execution.
     *
     * @return int
     */
    public function getRunResult()
    {
        return $this->runResult;
    }
}
