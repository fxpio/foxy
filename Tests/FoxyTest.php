<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;
use Foxy\Foxy;
use Foxy\Solver\SolverInterface;

/**
 * Tests for foxy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FoxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composer;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composerConfig;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var RootPackageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $package;

    protected function setUp()
    {
        $this->composer = $this->getMockBuilder('Composer\Composer')->disableOriginalConstructor()->getMock();
        $this->composerConfig = $this->getMockBuilder('Composer\Config')->disableOriginalConstructor()->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->package = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();

        $this->composer->expects($this->any())
            ->method('getPackage')
            ->willReturn($this->package);

        $this->composer->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->composerConfig);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertCount(2, Foxy::getSubscribedEvents());
    }

    public function testActivate()
    {
        $foxy = new Foxy();
        $foxy->activate($this->composer, $this->io);
        $this->assertTrue(true);
    }

    /**
     * @expectedException \Foxy\Exception\RuntimeException
     * @expectedExceptionMessage The asset manager "invalid_manager" doesn't exist
     */
    public function testActivateWithInvalidManager()
    {
        $this->package->expects($this->any())
            ->method('getConfig')
            ->willReturn(array(
                'foxy' => array(
                    'manager' => 'invalid_manager',
                ),
            ));

        $foxy = new Foxy();
        $foxy->activate($this->composer, $this->io);
    }

    public function testSolveAssets()
    {
        $event = new Event('solve_event', $this->composer, $this->io);
        /* @var SolverInterface|\PHPUnit_Framework_MockObject_MockObject $solver */
        $solver = $this->getMockBuilder('Foxy\Solver\SolverInterface')->getMock();
        $solver->expects($this->once())
            ->method('solve')
            ->with($this->composer, $this->io);

        $foxy = new Foxy();
        $foxy->setSolver($solver);
        $foxy->solveAssets($event);
    }
}
