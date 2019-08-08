<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Fallback;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Foxy\Config\Config;
use Foxy\Fallback\AssetFallback;

/**
 * Tests for composer fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AssetFallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fs;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $sfs;

    /**
     * @var string
     */
    protected $oldCwd;

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var AssetFallback
     */
    protected $assetFallback;

    protected function setUp()
    {
        parent::setUp();

        $this->oldCwd = getcwd();
        $this->cwd = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_asset_fallback_test_', true);
        $this->config = new Config(array(
            'fallback-asset' => true,
        ));
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->fs = $this->getMockBuilder('Composer\Util\Filesystem')->disableOriginalConstructor()->setMethods(array('remove'))->getMock();
        $this->sfs = new \Symfony\Component\Filesystem\Filesystem();
        $this->sfs->mkdir($this->cwd);
        chdir($this->cwd);

        $this->assetFallback = new AssetFallback($this->io, $this->config, 'package.json', $this->fs);
    }

    protected function tearDown()
    {
        parent::tearDown();

        chdir($this->oldCwd);
        $this->sfs->remove($this->cwd);
        $this->config = null;
        $this->io = null;
        $this->fs = null;
        $this->sfs = null;
        $this->assetFallback = null;
        $this->oldCwd = null;
        $this->cwd = null;
    }

    public function getSaveData()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider getSaveData
     *
     * @param bool $withPackageFile
     */
    public function testSave($withPackageFile)
    {
        if ($withPackageFile) {
            file_put_contents($this->cwd.'/package.json', '{}');
        }

        static::assertInstanceOf('Foxy\Fallback\AssetFallback', $this->assetFallback->save());
    }

    public function testRestoreWithDisableOption()
    {
        $config = new Config(array(
            'fallback-asset' => false,
        ));
        $assetFallback = new AssetFallback($this->io, $config, 'package.json', $this->fs);

        $this->io->expects(static::never())
            ->method('write')
        ;

        $this->fs->expects(static::never())
            ->method('remove')
        ;

        $assetFallback->restore();
    }

    public function getRestoreData()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider getRestoreData
     *
     * @param bool $withPackageFile
     */
    public function testRestore($withPackageFile)
    {
        $content = '{}';
        $path = $this->cwd.'/package.json';

        if ($withPackageFile) {
            file_put_contents($path, $content);
        }

        $this->io->expects(static::once())
            ->method('write')
        ;

        $this->fs->expects(static::once())
            ->method('remove')
            ->with('package.json')
        ;

        $this->assetFallback->save();
        $this->assetFallback->restore();

        if ($withPackageFile) {
            static::assertFileExists($path);
            static::assertSame($content, file_get_contents($path));
        } else {
            static::assertFileNotExists($path);
        }
    }
}
