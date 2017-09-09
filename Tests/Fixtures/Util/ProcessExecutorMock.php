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
     * @var int
     */
    public $mockExecuteReturnValue = 0;

    /**
     * @var string|null
     */
    public $mockExecuteOutputValue = null;

    /**
     * @var string|null
     */
    private $lastCommand;

    /**
     * @var string|null
     */
    private $lastOutput;

    /**
     * {@inheritdoc}
     */
    public function execute($command, &$output = null, $cwd = null)
    {
        $output = $this->mockExecuteOutputValue;
        $res = $this->mockExecuteReturnValue;

        $this->lastCommand = $command;
        $this->lastOutput = $output;

        // reset
        $this->mockExecuteReturnValue = 0;
        $this->mockExecuteOutputValue = null;

        return $res;
    }

    /**
     * Get the last executed command.
     *
     * @return string|null
     */
    public function getLastCommand()
    {
        return $this->lastCommand;
    }

    /**
     * Get the last executed output.
     *
     * @return string|null
     */
    public function getLastOutput()
    {
        return $this->lastOutput;
    }
}
