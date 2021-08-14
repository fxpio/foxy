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
 * Interface for the converter for asset syntax version to composer syntax version.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface VersionConverterInterface
{
    /**
     * Converts the asset version to composer version.
     *
     * @param string $version The asset version
     *
     * @return string The composer version
     */
    public function convertVersion($version);
}
