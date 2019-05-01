<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Fallback;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Foxy\Config\Config;

/**
 * Asset fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetFallback implements FallbackInterface
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var null|string
     */
    protected $originalContent;

    public function __construct(IOInterface $io, Config $config, $path, Filesystem $fs = null)
    {
        $this->io = $io;
        $this->config = $config;
        $this->path = $path;
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        if (file_exists($this->path) && is_file($this->path)) {
            $this->originalContent = file_get_contents($this->path);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function restore()
    {
        if (!$this->config->get('fallback-asset')) {
            return;
        }

        $this->io->write('<info>Fallback to previous state for the Asset package</info>');
        $this->fs->remove($this->path);

        if (null !== $this->originalContent) {
            file_put_contents($this->path, $this->originalContent);
        }
    }
}
