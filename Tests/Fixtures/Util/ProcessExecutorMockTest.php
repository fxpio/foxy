<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Json;

use Foxy\Tests\Fixtures\Util\ProcessExecutorMock;

/**
 * Tests for the process executor mock.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ProcessExecutorMockTest extends \PHPUnit\Framework\TestCase
{
    public function testExecuteWithoutExpectedValues()
    {
        $executor = new ProcessExecutorMock();

        $executor->execute('run', $output);

        $this->assertSame('run', $executor->getExecutedCommand(0));
        $this->assertNull($executor->getExecutedReturnedCode(0));
        $this->assertNull($executor->getExecutedOutput(0));

        $this->assertNull($executor->getExecutedCommand(1));
        $this->assertNull($executor->getExecutedReturnedCode(1));
        $this->assertNull($executor->getExecutedOutput(1));

        $this->assertSame('run', $executor->getLastCommand());
        $this->assertNull($executor->getLastReturnedCode());
        $this->assertNull($executor->getLastOutput());

        $this->assertNull($output);
    }

    public function testExecuteWithExpectedValues()
    {
        $executor = new ProcessExecutorMock();

        $executor->addExpectedValues(0, 'TEST');
        $executor->addExpectedValues(42, 'TEST 2');

        $executor->execute('run', $output);
        $executor->execute('run2', $output2);

        $this->assertSame('run', $executor->getExecutedCommand(0));
        $this->assertSame(0, $executor->getExecutedReturnedCode(0));
        $this->assertSame('TEST', $executor->getExecutedOutput(0));

        $this->assertSame('run2', $executor->getExecutedCommand(1));
        $this->assertSame(42, $executor->getExecutedReturnedCode(1));
        $this->assertSame('TEST 2', $executor->getExecutedOutput(1));

        $this->assertNull($executor->getExecutedCommand(2));
        $this->assertNull($executor->getExecutedReturnedCode(2));
        $this->assertNull($executor->getExecutedOutput(2));

        $this->assertSame('run2', $executor->getLastCommand());
        $this->assertSame(42, $executor->getLastReturnedCode());
        $this->assertSame('TEST 2', $executor->getLastOutput());

        $this->assertSame('TEST', $output);
        $this->assertSame('TEST 2', $output2);
    }
}
