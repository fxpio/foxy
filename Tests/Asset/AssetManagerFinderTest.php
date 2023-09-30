<?php

/**
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Asset;

use Foxy\Asset\AssetManagerFinder;
use PHPUnit\Framework\TestCase;

/**
 * Asset manager finder tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AssetManagerFinderTest extends TestCase
{
    public function testFindManagerWithValidManager()
    {
        $am = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();

        $am->expects(static::once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $amf = new AssetManagerFinder(array($am));
        $res = $amf->findManager('foo');

        static::assertSame($am, $res);
    }

    public function testFindManagerWithInvalidManager()
    {
        static::expectException('Foxy\Exception\RuntimeException');
        static::expectExceptionMessage('The asset manager "bar" doesn\'t exist');

        $am = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();

        $am->expects(static::once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $amf = new AssetManagerFinder(array($am));
        $amf->findManager('bar');
    }

    public function testFindManagerWithAutoManagerAndAvailableManagerByLockFile()
    {
        $am = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();

        $am->expects(static::once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $am->expects(static::once())
            ->method('hasLockFile')
            ->willReturn(true)
        ;

        $am->expects(static::never())
            ->method('isAvailable')
        ;

        $amf = new AssetManagerFinder(array($am));
        $res = $amf->findManager(null);

        static::assertSame($am, $res);
    }

    public function testFindManagerWithAutoManagerAndAvailableManagerByAvailability()
    {
        $am = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();

        $am->expects(static::once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $am->expects(static::once())
            ->method('hasLockFile')
            ->willReturn(false)
        ;

        $am->expects(static::once())
            ->method('isAvailable')
            ->willReturn(true)
        ;

        $amf = new AssetManagerFinder(array($am));
        $res = $amf->findManager(null);

        static::assertSame($am, $res);
    }

    public function testFindManagerWithAutoManagerAndNoAvailableManager()
    {
        static::expectException('Foxy\Exception\RuntimeException');
        static::expectExceptionMessage('No asset manager is found');

        $am = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();

        $am->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('foo')
        ;

        $am->expects(static::once())
            ->method('hasLockFile')
            ->willReturn(false)
        ;

        $am->expects(static::once())
            ->method('isAvailable')
            ->willReturn(false)
        ;

        $amf = new AssetManagerFinder(array($am));
        $amf->findManager(null);
    }
}
