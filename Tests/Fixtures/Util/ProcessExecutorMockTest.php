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

        static::assertSame('run', $executor->getExecutedCommand(0));
        static::assertNull($executor->getExecutedReturnedCode(0));
        static::assertNull($executor->getExecutedOutput(0));

        static::assertNull($executor->getExecutedCommand(1));
        static::assertNull($executor->getExecutedReturnedCode(1));
        static::assertNull($executor->getExecutedOutput(1));

        static::assertSame('run', $executor->getLastCommand());
        static::assertNull($executor->getLastReturnedCode());
        static::assertNull($executor->getLastOutput());

        static::assertNull($output);
    }

    public function testExecuteWithExpectedValues()
    {
        $executor = new ProcessExecutorMock();

        $executor->addExpectedValues(0, 'TEST');
        $executor->addExpectedValues(42, 'TEST 2');

        $executor->execute('run', $output);
        $executor->execute('run2', $output2);

        static::assertSame('run', $executor->getExecutedCommand(0));
        static::assertSame(0, $executor->getExecutedReturnedCode(0));
        static::assertSame('TEST', $executor->getExecutedOutput(0));

        static::assertSame('run2', $executor->getExecutedCommand(1));
        static::assertSame(42, $executor->getExecutedReturnedCode(1));
        static::assertSame('TEST 2', $executor->getExecutedOutput(1));

        static::assertNull($executor->getExecutedCommand(2));
        static::assertNull($executor->getExecutedReturnedCode(2));
        static::assertNull($executor->getExecutedOutput(2));

        static::assertSame('run2', $executor->getLastCommand());
        static::assertSame(42, $executor->getLastReturnedCode());
        static::assertSame('TEST 2', $executor->getLastOutput());

        static::assertSame('TEST', $output);
        static::assertSame('TEST 2', $output2);
    }
}
