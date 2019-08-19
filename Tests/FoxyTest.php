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
 *
 * @internal
 */
final class FoxyTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface
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
            ->willReturn($this->package)
        ;

        $this->composer->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->composerConfig)
        ;

        $rm = $this->getMockBuilder('Composer\Repository\RepositoryManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects($this->any())
            ->method('getRepositoryManager')
            ->willReturn($rm)
        ;

        $im = $this->getMockBuilder('Composer\Installer\InstallationManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects($this->any())
            ->method('getInstallationManager')
            ->willReturn($im)
        ;
    }

    public function testGetSubscribedEvents()
    {
        $this->assertCount(4, Foxy::getSubscribedEvents());
    }

    public function testActivate()
    {
        $foxy = new Foxy();
        $foxy->activate($this->composer, $this->io);
        $foxy->init();
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
            ))
        ;

        $foxy = new Foxy();
        $foxy->activate($this->composer, $this->io);
    }

    public function getSolveAssetsData()
    {
        return array(
            array('solve_event_install', false),
            array('solve_event_update', true),
        );
    }

    /**
     * @dataProvider getSolveAssetsData
     *
     * @param string $eventName
     * @param bool   $expectedUpdatable
     */
    public function testSolveAssets($eventName, $expectedUpdatable)
    {
        $event = new Event($eventName, $this->composer, $this->io);
        /** @var \PHPUnit_Framework_MockObject_MockObject|SolverInterface $solver */
        $solver = $this->getMockBuilder('Foxy\Solver\SolverInterface')->getMock();
        $solver->expects($this->once())
            ->method('setUpdatable')
            ->with($expectedUpdatable)
        ;
        $solver->expects($this->once())
            ->method('solve')
            ->with($this->composer, $this->io)
        ;

        $foxy = new Foxy();
        $foxy->setSolver($solver);
        $foxy->solveAssets($event);
    }
}
