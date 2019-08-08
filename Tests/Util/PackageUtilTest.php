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
 *
 * @internal
 */
final class PackageUtilTest extends \PHPUnit\Framework\TestCase
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

        static::assertArrayHasKey('packages', $lockDataLoaded);
        static::assertArrayHasKey('packages-dev', $lockDataLoaded);
        static::assertEquals($lockDataLoaded['packages'], $expectedPackages);
        static::assertEquals($lockDataLoaded['packages-dev'], $expectedDevPackages);
    }

    public function testLoadLockPackagesWithoutPackages()
    {
        static::assertSame(array(), PackageUtil::loadLockPackages(array()));
    }

    public function testConvertLockAlias()
    {
        $lockData = array(
            'aliases' => array(
                array(
                    'alias' => '1.0.0',
                    'alias_normalized' => '1.0.0.0',
                    'version' => 'dev-feature/1.0-test',
                    'package' => 'foo/bar',
                ),
                array(
                    'alias' => '2.2.0',
                    'alias_normalized' => '2.2.0.0',
                    'version' => 'dev-feature/2.2-test',
                    'package' => 'foo/baz',
                ),
            ),
        );
        $expectedAliases = array(
            'foo/bar' => array(
                'dev-feature/1.0-test' => array(
                    'alias' => '1.0.0',
                    'alias_normalized' => '1.0.0.0',
                ),
            ),
            'foo/baz' => array(
                'dev-feature/2.2-test' => array(
                    'alias' => '2.2.0',
                    'alias_normalized' => '2.2.0.0',
                ),
            ),
        );

        $convertedAliases = PackageUtil::convertLockAlias($lockData);

        static::assertArrayHasKey('aliases', $convertedAliases);
        static::assertEquals($convertedAliases['aliases'], $expectedAliases);
    }
}
