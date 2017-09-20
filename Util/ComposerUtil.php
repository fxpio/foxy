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

use Foxy\Exception\RuntimeException;

/**
 * Helper for Composer.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerUtil
{
    /**
     * Validate the composer version.
     *
     * @param string $requiredVersion The composer required version
     * @param string $composerVersion The composer version
     */
    public static function validateVersion($requiredVersion, $composerVersion)
    {
        if (false === strpos($composerVersion, '@') && !version_compare($composerVersion, $requiredVersion, '>=')) {
            $msg = 'Foxy requires the Composer\'s minimum version "%s", current version is "%s"';

            throw new RuntimeException(sprintf($msg, $requiredVersion, $composerVersion));
        }
    }
}
