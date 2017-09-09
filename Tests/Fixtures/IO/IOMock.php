<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Fixtures\IO;

use Composer\IO\BaseIO;
use Composer\IO\IOInterface;

/**
 * Mock of IO.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IOMock extends BaseIO
{
    /**
     * @var bool
     */
    protected $verbose;

    /**
     * @var array
     */
    protected $traces;

    /**
     * Constructor.
     *
     * @param bool $verbose
     */
    public function __construct($verbose)
    {
        $this->verbose = $verbose;
        $this->traces = array();
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return $this->verbose;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = true, $verbosity = IOInterface::NORMAL)
    {
        $pos = max(count($this->traces) - 1, 0);
        if (isset($this->traces[$pos])) {
            $messages = $this->traces[$pos].$messages;
        }
        $this->traces[$pos] = $messages;
        if ($newline) {
            $this->traces[] = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($messages, $newline = true, $verbosity = IOInterface::NORMAL)
    {
        $this->write($messages, $newline, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function overwrite($messages, $newline = true, $size = null, $verbosity = IOInterface::NORMAL)
    {
        $pos = max(count($this->traces) - 1, 0);
        $this->traces[$pos] = $messages;
        if ($newline) {
            $this->traces[] = '';
        }
    }

    public function overwriteError($messages, $newline = true, $size = null, $verbosity = IOInterface::NORMAL)
    {
        $this->overwrite($messages, $newline, $size, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation($question, $default = true)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askAndHideAnswer($question)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function select($question, $choices, $default, $attempts = false, $errorMessage = 'Value "%s" is invalid', $multiselect = false)
    {
        return $default;
    }

    /**
     * Gets the taces.
     *
     * @return array
     */
    public function getTraces()
    {
        return $this->traces;
    }
}
