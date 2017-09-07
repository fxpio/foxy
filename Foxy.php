<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer plugin.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Foxy implements PluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }
}
