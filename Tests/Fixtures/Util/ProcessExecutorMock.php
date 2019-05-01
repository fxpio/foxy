<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Fixtures\Util;

use Composer\Util\ProcessExecutor;

/**
 * Mock of ProcessExecutor.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ProcessExecutorMock extends ProcessExecutor
{
    /**
     * @var array
     */
    private $expectedValues = array();

    /**
     * @var array
     */
    private $executedCommands = array();

    /**
     * @var int
     */
    private $position = 0;

    /**
     * {@inheritdoc}
     */
    public function execute($command, &$output = null, $cwd = null)
    {
        $expected = isset($this->expectedValues[$this->position])
            ? $this->expectedValues[$this->position]
            : array(null, $output);

        list($returnedCode, $output) = $expected;
        $this->executedCommands[] = array($command, $returnedCode, $output);
        ++$this->position;

        return $returnedCode;
    }

    /**
     * @param int  $returnedCode The returned code
     * @param null $output       The output
     *
     * @return self
     */
    public function addExpectedValues($returnedCode = 0, $output = null)
    {
        $this->expectedValues[] = array($returnedCode, $output);

        return $this;
    }

    /**
     * Get the executed command.
     *
     * @param int $position The position of executed command
     *
     * @return null|string
     */
    public function getExecutedCommand($position)
    {
        return $this->getExecutedValue($position, 0);
    }

    /**
     * Get the executed returned code.
     *
     * @param int $position The position of executed command
     *
     * @return null|int
     */
    public function getExecutedReturnedCode($position)
    {
        return $this->getExecutedValue($position, 1);
    }

    /**
     * Get the executed command.
     *
     * @param int $position The position of executed command
     *
     * @return null|string
     */
    public function getExecutedOutput($position)
    {
        return $this->getExecutedValue($position, 2);
    }

    /**
     * Get the last executed command.
     *
     * @return null|string
     */
    public function getLastCommand()
    {
        return $this->getExecutedCommand(\count($this->executedCommands) - 1);
    }

    /**
     * Get the last executed returned code.
     *
     * @return null|int
     */
    public function getLastReturnedCode()
    {
        return $this->getExecutedReturnedCode(\count($this->executedCommands) - 1);
    }

    /**
     * Get the last executed output.
     *
     * @return null|string
     */
    public function getLastOutput()
    {
        return $this->getExecutedOutput(\count($this->executedCommands) - 1);
    }

    /**
     * Get the value of the executed command.
     *
     * @param int $position The position
     * @param int $index    The index of value
     *
     * @return null|int|string
     */
    private function getExecutedValue($position, $index)
    {
        return isset($this->executedCommands[$position])
            ? $this->executedCommands[$position][$index]
            : null;
    }
}
