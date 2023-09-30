<?php

/**
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
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

    /**
     * @param \Composer\IO\IOInterface       $io     The console IO
     * @param \Foxy\Config\Config            $config The Fox config
     * @param string                         $path   Path to the asset
     * @param null|\Composer\Util\Filesystem $fs     Filsesysem object.
     */
    public function __construct(IOInterface $io, Config $config, string $path, Filesystem $fs = null)
    {
        $this->io = $io;
        $this->config = $config;
        $this->path = $path;
        $this->fs = $fs ?: new Filesystem();
    }

    // phpcs:disable PEAR.Commenting.FunctionComment.MissingReturn
    // phpcs:disable PEAR.Commenting.FunctionComment.MissingParamTag

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

    // phpcs:enable PEAR.Commenting.FunctionComment.MissingReturn
    // phpcs:enable PEAR.Commenting.FunctionComment.MissingParamTag
}
