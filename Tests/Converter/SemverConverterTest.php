<?php

/**
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Converter;

use Foxy\Converter\SemverConverter;
use Foxy\Converter\VersionConverterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the conversion of Semver syntax to composer syntax.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SemverConverterTest extends TestCase
{
    /**
     * @var VersionConverterInterface
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->converter = new SemverConverter();
    }

    protected function tearDown(): void
    {
        $this->converter = null;
    }

    /**
     * @dataProvider getTestVersions
     *
     * @param string $semver
     * @param string $composer
     */
    public function testConverter($semver, $composer)
    {
        static::assertEquals($composer, $this->converter->convertVersion($semver));

        if (!ctype_alpha((string) $semver) && !\in_array($semver, array(null, ''), true)) {
            static::assertEquals('v'.$composer, $this->converter->convertVersion('v'.$semver));
        }
    }

    public function getTestVersions()
    {
        return array(
            array('1.2.3', '1.2.3'),
            array('1.2.3alpha', '1.2.3-alpha1'),
            array('1.2.3-alpha', '1.2.3-alpha1'),
            array('1.2.3a', '1.2.3-alpha1'),
            array('1.2.3a1', '1.2.3-alpha1'),
            array('1.2.3-a', '1.2.3-alpha1'),
            array('1.2.3-a1', '1.2.3-alpha1'),
            array('1.2.3b', '1.2.3-beta1'),
            array('1.2.3b1', '1.2.3-beta1'),
            array('1.2.3-b', '1.2.3-beta1'),
            array('1.2.3-b1', '1.2.3-beta1'),
            array('1.2.3beta', '1.2.3-beta1'),
            array('1.2.3-beta', '1.2.3-beta1'),
            array('1.2.3beta1', '1.2.3-beta1'),
            array('1.2.3-beta1', '1.2.3-beta1'),
            array('1.2.3rc1', '1.2.3-RC1'),
            array('1.2.3-rc1', '1.2.3-RC1'),
            array('1.2.3rc2', '1.2.3-RC2'),
            array('1.2.3-rc2', '1.2.3-RC2'),
            array('1.2.3rc.2', '1.2.3-RC.2'),
            array('1.2.3-rc.2', '1.2.3-RC.2'),
            array('1.2.3+0', '1.2.3-patch0'),
            array('1.2.3-0', '1.2.3-patch0'),
            array('1.2.3pre', '1.2.3-beta1'),
            array('1.2.3-pre', '1.2.3-beta1'),
            array('1.2.3dev', '1.2.3-dev'),
            array('1.2.3-dev', '1.2.3-dev'),
            array('1.2.3+build2012', '1.2.3-patch2012'),
            array('1.2.3-build2012', '1.2.3-patch2012'),
            array('1.2.3+build.2012', '1.2.3-patch.2012'),
            array('1.2.3-build.2012', '1.2.3-patch.2012'),
            array('1.3.0–rc30.79', '1.3.0-RC30.79'),
            array('1.2.3-SNAPSHOT', '1.2.3-dev'),
            array('1.2.3-20123131.3246', '1.2.3-patch20123131.3246'),
            array('1.x.x-dev', '1.x-dev'),
            array('20170124.0.0', '20170124.000000'),
            array('20170124.1.0', '20170124.001000'),
            array('20170124.1.1', '20170124.001001'),
            array('20170124.100.200', '20170124.100200'),
            array('20170124.0', '20170124.000000'),
            array('20170124.1', '20170124.001000'),
            array('20170124', '20170124'),
            array('latest', 'default || *'),
            array(null, '*'),
            array('', '*'),
        );
    }
}
