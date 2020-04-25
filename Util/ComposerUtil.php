<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Util;

use Composer\Installer\InstallerEvents;
use Composer\Semver\Semver;
use Foxy\Exception\RuntimeException;

/**
 * Helper for Composer.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerUtil
{
    /**
     * Get the event name to init the plugin.
     *
     * @return string
     */
    public static function getInitEventName()
    {
        return \defined('Composer\Installer\InstallerEvents::PRE_DEPENDENCIES_SOLVING')
            ? InstallerEvents::PRE_DEPENDENCIES_SOLVING : InstallerEvents::PRE_OPERATIONS_EXEC;
    }

    /**
     * Validate the composer version.
     *
     * @param string $requiredVersion The composer required version
     * @param string $composerVersion The composer version
     */
    public static function validateVersion($requiredVersion, $composerVersion)
    {
        $isBranch = false !== strpos($composerVersion, '@');
        $isSnapshot = (bool) preg_match('/^[0-9a-f]{40}$/i', $composerVersion);

        if (!$isBranch && !$isSnapshot && !Semver::satisfies($composerVersion, $requiredVersion)) {
            $msg = 'Foxy requires the Composer\'s minimum version "%s", current version is "%s"';

            throw new RuntimeException(sprintf($msg, $requiredVersion, $composerVersion));
        }
    }
}
