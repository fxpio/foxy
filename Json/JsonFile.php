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
     * @var int|null
     */
    private $indent;

    /**
     * @var int|null
     */
    private static $encodeIndent;

    /**
     * Get the indent for this json file.
     *
     * @return int
     */
    public function getIndent()
    {
        if (null === $this->indent) {
            $content = $this->exists() ? file_get_contents($this->getPath()) : '';
            $this->indent = JsonFormatter::getIndent($content);
        }

        return $this->indent;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();
        $this->getIndent();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $hash, $options = 448)
    {
        $prevIndent = self::$encodeIndent;
        self::$encodeIndent = $this->getIndent();
        parent::write($hash, $options);
        self::$encodeIndent = $prevIndent;
    }

    /**
     * {@inheritdoc}
     */
    public static function encode($data, $options = 448)
    {
        $result = parent::encode($data, $options);

        return JsonFormatter::format($result, self::$encodeIndent, false);
    }
}
