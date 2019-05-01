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

use Composer\Json\JsonFile as BaseJsonFile;

/**
 * The JSON file.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class JsonFile extends BaseJsonFile
{
    /**
     * @var string[]
     */
    private $arrayKeys;

    /**
     * @var int
     */
    private $indent;

    /**
     * @var string[]
     */
    private static $encodeArrayKeys = array();

    /**
     * @var int
     */
    private static $encodeIndent = JsonFormatter::DEFAULT_INDENT;

    /**
     * Get the list of keys to be retained with an array representation if they are empty.
     *
     * @return string[]
     */
    public function getArrayKeys()
    {
        if (null === $this->arrayKeys) {
            $this->parseOriginalContent();
        }

        return $this->arrayKeys;
    }

    /**
     * Get the indent for this json file.
     *
     * @return int
     */
    public function getIndent()
    {
        if (null === $this->indent) {
            $this->parseOriginalContent();
        }

        return $this->indent;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();
        $this->getArrayKeys();
        $this->getIndent();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $hash, $options = 448)
    {
        self::$encodeArrayKeys = $this->getArrayKeys();
        self::$encodeIndent = $this->getIndent();
        parent::write($hash, $options);
        self::$encodeArrayKeys = array();
        self::$encodeIndent = JsonFormatter::DEFAULT_INDENT;
    }

    /**
     * {@inheritdoc}
     */
    public static function encode($data, $options = 448)
    {
        $result = parent::encode($data, $options);

        return JsonFormatter::format($result, self::$encodeArrayKeys, self::$encodeIndent, false);
    }

    /**
     * Parse the original content.
     */
    private function parseOriginalContent()
    {
        $content = $this->exists() ? file_get_contents($this->getPath()) : '';
        $this->arrayKeys = JsonFormatter::getArrayKeys($content);
        $this->indent = JsonFormatter::getIndent($content);
    }
}
