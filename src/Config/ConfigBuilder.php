<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Config;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;

/**
 * Plugin Config builder.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class ConfigBuilder
{
    /**
     * Build the config of plugin.
     *
     * @param Composer         $composer The composer
     * @param array            $defaults The default values
     * @param null|IOInterface $io       The composer input/output
     *
     * @return Config
     */
    public static function build(Composer $composer, array $defaults = array(), $io = null)
    {
        $config = self::getConfigBase($composer, $io);

        return new Config($config, $defaults);
    }

    /**
     * Get the base of data.
     *
     * @param Composer         $composer The composer
     * @param null|IOInterface $io       The composer input/output
     *
     * @return array
     */
    private static function getConfigBase(Composer $composer, $io = null)
    {
        $globalPackageConfig = self::getGlobalConfig($composer, 'composer', $io);
        $globalConfig = self::getGlobalConfig($composer, 'config', $io);
        $packageConfig = $composer->getPackage()->getConfig();
        $packageConfig = isset($packageConfig['foxy']) && \is_array($packageConfig['foxy'])
            ? $packageConfig['foxy']
            : array();

        return array_merge($globalPackageConfig, $globalConfig, $packageConfig);
    }

    /**
     * Get the data of the global config.
     *
     * @param Composer         $composer The composer
     * @param string           $filename The filename
     * @param null|IOInterface $io       The composer input/output
     *
     * @return array
     */
    private static function getGlobalConfig(Composer $composer, $filename, $io = null)
    {
        $home = self::getComposerHome($composer);
        $file = new JsonFile($home.'/'.$filename.'.json');
        $config = array();

        if ($file->exists()) {
            $data = $file->read();

            if (isset($data['config']['foxy']) && \is_array($data['config']['foxy'])) {
                $config = $data['config']['foxy'];

                if ($io instanceof IOInterface && $io->isDebug()) {
                    $io->writeError('Loading Foxy config in file '.$file->getPath());
                }
            }
        }

        return $config;
    }

    /**
     * Get the home directory of composer.
     *
     * @param Composer $composer The composer
     *
     * @return string
     */
    private static function getComposerHome(Composer $composer)
    {
        return null !== $composer->getConfig() && $composer->getConfig()->has('home')
            ? $composer->getConfig()->get('home')
            : '';
    }
}
