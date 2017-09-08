<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Config;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Foxy\Config\ConfigBuilder;

/**
 * Tests for config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
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
        $this->composer = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();
        $this->composerConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->io = $this->getMockBuilder(IOInterface::class)->getMock();
        $this->package = $this->getMockBuilder(RootPackageInterface::class)->getMock();

        $this->composer->expects($this->any())
            ->method('getPackage')
            ->willReturn($this->package);

        $this->composer->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->composerConfig);
    }

    public function getDataForGetConfig()
    {
        return array(
            array('foo',                 42,                           42),
            array('bar',                 'foo',                        'empty'),
            array('baz',                 false,                        true),
            array('test',                0,                            0),
            array('global-composer-foo', 90,                           0),
            array('global-composer-bar', 70,                           0),
            array('global-config-foo',   23,                           0),
            array('env-boolean',         false,                        true,    'FOXY__ENV_BOOLEAN=false'),
            array('env-integer',         -32,                          0,       'FOXY__ENV_INTEGER=-32'),
            array('env-json',            array('foo' => 'bar'),        array(), 'FOXY__ENV_JSON="{"foo": "bar"}"'),
            array('env-json-array',      array(array('foo' => 'bar')), array(), 'FOXY__ENV_JSON_ARRAY="[{"foo": "bar"}]"'),
            array('env-string',          'baz',                        'foo',   'FOXY__ENV_STRING=baz'),
            array('test-p1',             'def',                        'def',   null, array()),
            array('test-p1',             'def',                        'def',   null, array('test-p1' => 'ok')),
            array('test-p1',             'ok',                         null,    null, array('test-p1' => 'ok')),
        );
    }

    /**
     * @dataProvider getDataForGetConfig
     *
     * @param string      $key      The key
     * @param mixed       $expected The expected value
     * @param mixed|null  $default  The default value
     * @param string|null $env      The env variable
     * @param array       $defaults The configured default values
     */
    public function testGetConfig($key, $expected, $default = null, $env = null, array $defaults = array())
    {
        // add env variables
        if (null !== $env) {
            putenv($env);
        }

        $globalPath = realpath(__DIR__.'/../Fixtures/package/global');
        $this->composerConfig->expects($this->any())
            ->method('has')
            ->with('home')
            ->willReturn(true);

        $this->composerConfig->expects($this->any())
            ->method('get')
            ->with('home')
            ->willReturn($globalPath);

        $this->package->expects($this->any())
            ->method('getConfig')
            ->willReturn(array(
                'foxy' => array(
                    'bar' => 'foo',
                    'baz' => false,
                    'env-foo' => 55,
                ),
            ));

        if (0 === strpos($key, 'global-')) {
            $this->io->expects($this->atLeast(2))
                ->method('isDebug')
                ->willReturn(true);

            $this->io->expects($this->at(1))
                ->method('writeError')
                ->with(sprintf('Loading Foxy config in file %s/composer.json', $globalPath));
            $this->io->expects($this->at(3))
                ->method('writeError')
                ->with(sprintf('Loading Foxy config in file %s/config.json', $globalPath));
        }

        $config = ConfigBuilder::build($this->composer, $defaults, $this->io);
        $value = $config->get($key, $default);

        // remove env variables
        if (null !== $env) {
            $envKey = substr($env, 0, strpos($env, '='));
            putenv($envKey);
            $this->assertFalse(getenv($envKey));
        }

        $this->assertSame($expected, $value);
        // test cache
        $this->assertSame($expected, $config->get($key, $default));
    }

    public function getDataForGetArrayConfig()
    {
        return array(
            array('foo', array(),   array()),
            array('foo', array(42), array(42)),
            array('foo', array(42), array(), array('foo' => array(42))),
        );
    }

    /**
     * @dataProvider getDataForGetArrayConfig
     *
     * @param string $key      The key
     * @param array  $expected The expected value
     * @param array  $default  The default value
     * @param array  $defaults The configured default values
     */
    public function testGetArrayConfig($key, array $expected, array $default, array $defaults = array())
    {
        $config = ConfigBuilder::build($this->composer, $defaults, $this->io);

        $this->assertSame($expected, $config->getArray($key, $default));
    }

    /**
     * @expectedException \Foxy\Exception\RuntimeException
     * @expectedExceptionMessage The "FOXY__ENV_JSON" environment variable isn't a valid JSON
     */
    public function testGetEnvConfigWithInvalidJson()
    {
        putenv('FOXY__ENV_JSON="{"foo"}"');
        $config = ConfigBuilder::build($this->composer, array(), $this->io);
        $ex = null;

        try {
            $config->get('env-json');
        } catch (\Exception $e) {
            $ex = $e;
        }

        putenv('FOXY__ENV_JSON');
        $this->assertFalse(getenv('FOXY__ENV_JSON'));

        if (null === $ex) {
            throw new \Exception('The expected exception was not thrown');
        }

        throw $ex;
    }
}
