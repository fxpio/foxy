<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Util;

use Composer\Package\CompletePackage;
use Foxy\Util\PackageUtil;

/**
 * Tests for package util.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PackageUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadLockPackages()
    {
        $lockData = array(
            'packages' => array(
                array(
                    'name' => 'foo/bar',
                    'version' => '1.0.0.0',
                ),
            ),
            'packages-dev' => array(
                array(
                    'name' => 'bar/foo',
                    'version' => '1.0.0.0',
                ),
            ),
        );

        $package = new CompletePackage('foo/bar', '1.0.0.0', '1.0.0.0');
        $package->setType('library');

        $packageDev = new CompletePackage('bar/foo', '1.0.0.0', '1.0.0.0');
        $packageDev->setType('library');

        $expectedPackages = array(
            $package,
        );
        $expectedDevPackages = array(
            $packageDev,
        );

        $lockDataLoaded = PackageUtil::loadLockPackages($lockData);

        $this->assertArrayHasKey('packages', $lockDataLoaded);
        $this->assertArrayHasKey('packages-dev', $lockDataLoaded);
        $this->assertEquals($lockDataLoaded['packages'], $expectedPackages);
        $this->assertEquals($lockDataLoaded['packages-dev'], $expectedDevPackages);
    }

    public function testLoadLockPackagesWithoutPackages()
    {
        $this->assertSame(array(), PackageUtil::loadLockPackages(array()));
    }
}
