<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Converter;

/**
 * Converter for Semver syntax version to composer syntax version.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SemverConverter implements VersionConverterInterface
{
    public function convertVersion($version)
    {
        if (\in_array($version, array(null, '', 'latest'), true)) {
            return ('latest' === $version ? 'default || ' : '').'*';
        }

        $version = str_replace('–', '-', $version);
        $prefix = preg_match('/^[a-z]/', $version) && 0 !== strpos($version, 'dev-') ? substr($version, 0, 1) : '';
        $version = substr($version, \strlen($prefix));
        $version = SemverUtil::convertVersionMetadata($version);
        $version = SemverUtil::convertDateVersion($version);

        return $prefix.$version;
    }
}
