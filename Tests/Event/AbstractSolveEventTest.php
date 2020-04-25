<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Event;

use Composer\Package\PackageInterface;
use Foxy\Event\AbstractSolveEvent;

/**
 * Tests for solve events.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSolveEventTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $assetDir;

    /**
     * @var PackageInterface[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $packages;

    protected function setUp()
    {
        $this->assetDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_event_test_', true);
        $this->packages = array(
            $this->getMockBuilder('Composer\Package\PackageInterface')->getMock(),
        );
    }

    protected function tearDown()
    {
        $this->assetDir = null;
        $this->packages = null;
    }

    /**
     * Get the event instance.
     *
     * @return AbstractSolveEvent
     */
    abstract public function getEvent();

    public function testGetAssetDir()
    {
        $event = $this->getEvent();
        static::assertSame($this->assetDir, $event->getAssetDir());
    }

    public function testGetPackages()
    {
        $event = $this->getEvent();
        static::assertSame($this->packages, $event->getPackages());
    }
}
