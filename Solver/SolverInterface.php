<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Solver;

use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * Interface of solver.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SolverInterface
{
    /**
     * Solve the asset dependencies.
     *
     * @param Composer    $composer The composer
     * @param IOInterface $io       The IO
     */
    public function solve(Composer $composer, IOInterface $io);
}
