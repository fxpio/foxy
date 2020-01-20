<?php

namespace Foxy\Tests\Asset;

use Foxy\Asset\AssetManagerResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssetManagerResultTest extends TestCase
{
    public function testCantConstructWithErrorAndWithoutMessage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1591974481);

        new AssetManagerResult('asd', 1);
    }

    public function testGetConstructionParametersThroughGetters()
    {
        $expectedCommand = 'asd';
        $expectedExitCode = 1;
        $expectedErrorReason = 'wub wub wub';

        $subject = new AssetManagerResult($expectedCommand, $expectedExitCode, $expectedErrorReason);

        static::assertSame($expectedCommand, $subject->getCommand());
        static::assertSame($expectedExitCode, $subject->getExitCode());
        static::assertSame($expectedErrorReason, $subject->getErrorReason());
    }
}
