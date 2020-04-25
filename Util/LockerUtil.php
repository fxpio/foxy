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

use Composer\Composer;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Locker;
use Composer\Repository\RepositoryManager;

/**
 * Helper for Locker.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class LockerUtil
{
    /**
     * Get the locker.
     *
     * @param string $composerFile
     *
     * @return Locker
     */
    public static function getLocker(IOInterface $io, RepositoryManager $rm, InstallationManager $im, $composerFile)
    {
        $lockFile = str_replace('.json', '.lock', $composerFile);
        // @codeCoverageIgnoreStart
        return \defined('Composer\Composer::RUNTIME_API_VERSION') && '2.0.0' === Composer::RUNTIME_API_VERSION
            ? new Locker($io, new JsonFile($lockFile, null, $io), $im, file_get_contents($composerFile))
            : new Locker($io, new JsonFile($lockFile, null, $io), $rm, $im, file_get_contents($composerFile));
    }
}
