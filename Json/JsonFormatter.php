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
    const ARRAY_KEYS_REGEX = '/["\']([\w\d\_\-\.]+)["\']:\s\[\]/';
    const INDENT_REGEX = '/^[\{\[][\r\n]([ ]+)["\']/';

    /**
     * Get the list of keys to be retained with an array representation if they are empty.
     *
     * @param string $content The content
     *
     * @return string[]
     */
    public static function getArrayKeys($content)
    {
        preg_match_all(self::ARRAY_KEYS_REGEX, trim($content), $matches);

        return !empty($matches) ? $matches[1] : array();
    }

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
        preg_match(self::INDENT_REGEX, trim($content), $matches);

        if (!empty($matches)) {
            $indent = \strlen($matches[1]);
        }

        return $indent;
    }

    /**
     * Format the data in JSON.
     *
     * @param string   $json       The original JSON
     * @param string[] $arrayKeys  The list of keys to be retained with an array representation if they are empty
     * @param int      $indent     The space count for indent
     * @param bool     $formatJson Check if the json must be formatted
     *
     * @return string
     */
    public static function format($json, array $arrayKeys = array(), $indent = self::DEFAULT_INDENT, $formatJson = true)
    {
        if ($formatJson) {
            $json = ComposerJsonFormatter::format($json, true, true);
        }

        if (4 !== $indent) {
            $json = str_replace('    ', sprintf('%'.$indent.'s', ''), $json);
        }

        return self::replaceArrayByMap($json, $arrayKeys);
    }

    /**
     * Replace the empty array by empty map.
     *
     * @param string   $json      The original JSON
     * @param string[] $arrayKeys The list of keys to be retained with an array representation if they are empty
     *
     * @return string
     */
    private static function replaceArrayByMap($json, array $arrayKeys)
    {
        preg_match_all(self::ARRAY_KEYS_REGEX, $json, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (!\in_array($match[1], $arrayKeys, true)) {
                $replace = str_replace('[]', '{}', $match[0]);
                $json = str_replace($match[0], $replace, $json);
            }
        }

        return $json;
    }
}
