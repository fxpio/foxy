<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Json;

use Composer\Json\JsonFormatter as ComposerJsonFormatter;

/**
 * Formats JSON strings with a custom indent.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class JsonFormatter
{
    const DEFAULT_INDENT = 4;

    /**
     * Get the indent of file.
     *
     * @param string $content The content
     *
     * @return int
     */
    public static function getIndent($content)
    {
        $indent = self::DEFAULT_INDENT;
        preg_match('/^[\{\[][\r\n]([ ]+)["\']/', trim($content), $matches);

        if (!empty($matches)) {
            $indent = strlen($matches[1]);
        }

        return $indent;
    }

    /**
     * Format the data in JSON.
     *
     * @param string $json       The original JSON
     * @param int    $indent     The space count for indent
     * @param bool   $formatJson Check if the json must be formatted
     *
     * @return string
     */
    public static function format($json, $indent = self::DEFAULT_INDENT, $formatJson = true)
    {
        if ($formatJson) {
            $json = ComposerJsonFormatter::format($json, true, true);
        }

        if (4 !== $indent) {
            $json = str_replace('    ', sprintf('%'.$indent.'s', ''), $json);
        }

        return preg_replace('/\:\s\[\]/', ': {}', $json);
    }
}
