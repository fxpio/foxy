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
use Foxy\Config\Config;
use Foxy\Exception\RuntimeException;

/**
 * Composer fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerFallback implements ComposerFallbackInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config $config The config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Composer $composer, IOInterface $io)
    {
        if (!$this->config->get('fallback-composer')) {
            return;
        }

        throw new RuntimeException('The fallback for the Composer lock file and its dependencies is not implemented currently');
    }
}
