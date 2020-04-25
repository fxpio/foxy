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

use Composer\Composer;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Foxy\Config\Config;
use Foxy\Fallback\ComposerFallback;
use Foxy\Util\LockerUtil;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Tests for composer fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ComposerFallbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composer;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $input;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fs;

    /**
     * @var Installer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $installer;

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
     * @var ComposerFallback
     */
    protected $composerFallback;

    protected function setUp()
    {
        parent::setUp();

        $this->oldCwd = getcwd();
        $this->cwd = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_composer_fallback_test_', true);
        $this->config = new Config(array(
            'fallback-composer' => true,
        ));
        $this->composer = $this->getMockBuilder('Composer\Composer')->disableOriginalConstructor()->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->input = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')->getMock();
        $this->fs = $this->getMockBuilder('Composer\Util\Filesystem')->disableOriginalConstructor()->setMethods(array('remove'))->getMock();
        $this->installer = $this->getMockBuilder('Composer\Installer')->disableOriginalConstructor()->setMethods(array('run'))->getMock();
        $this->sfs = new \Symfony\Component\Filesystem\Filesystem();
        $this->sfs->mkdir($this->cwd);
        chdir($this->cwd);

        $this->composerFallback = new ComposerFallback($this->composer, $this->io, $this->config, $this->input, $this->fs, $this->installer);
    }

    protected function tearDown()
    {
        parent::tearDown();

        chdir($this->oldCwd);
        $this->sfs->remove($this->cwd);
        $this->config = null;
        $this->composer = null;
        $this->io = null;
        $this->input = null;
        $this->fs = null;
        $this->installer = null;
        $this->sfs = null;
        $this->composerFallback = null;
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
     * @param bool $withLockFile
     */
    public function testSave($withLockFile)
    {
        $rm = $this->getMockBuilder('Composer\Repository\RepositoryManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects(static::any())
            ->method('getRepositoryManager')
            ->willReturn($rm)
        ;

        $im = $this->getMockBuilder('Composer\Installer\InstallationManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects(static::any())
            ->method('getInstallationManager')
            ->willReturn($im)
        ;

        file_put_contents($this->cwd.'/composer.json', '{}');

        if ($withLockFile) {
            file_put_contents($this->cwd.'/composer.lock', json_encode(array('content-hash' => 'HASH_VALUE')));
        }

        static::assertInstanceOf('Foxy\Fallback\ComposerFallback', $this->composerFallback->save());
    }

    public function testRestoreWithDisableOption()
    {
        $config = new Config(array(
            'fallback-composer' => false,
        ));
        $composerFallback = new ComposerFallback($this->composer, $this->io, $config, $this->input);

        $this->io->expects(static::never())
            ->method('write')
        ;

        $composerFallback->restore();
    }

    public function getRestoreData()
    {
        return array(
            array(array()),
            array(array(
                array(
                    'name' => 'foo/bar',
                    'version' => '1.0.0.0',
                ),
            )),
        );
    }

    /**
     * @dataProvider getRestoreData
     */
    public function testRestore(array $packages)
    {
        $composerFile = 'composer.json';
        $composerContent = '{}';
        $lockFile = 'composer.lock';
        $vendorDir = $this->cwd.'/vendor/';

        file_put_contents($this->cwd.'/'.$composerFile, $composerContent);
        file_put_contents($this->cwd.'/'.$lockFile, json_encode(array(
            'content-hash' => 'HASH_VALUE',
            'packages' => $packages,
            'packages-dev' => array(),
            'prefer-stable' => true,
        )));

        $rm = $this->getMockBuilder('Composer\Repository\RepositoryManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects(static::any())
            ->method('getRepositoryManager')
            ->willReturn($rm)
        ;

        $im = $this->getMockBuilder('Composer\Installer\InstallationManager')->disableOriginalConstructor()->getMock();
        $this->composer->expects(static::any())
            ->method('getInstallationManager')
            ->willReturn($im)
        ;

        $this->io->expects(static::once())
            ->method('write')
        ;

        $locker = LockerUtil::getLocker($this->io, $rm, $im, $composerFile);

        $this->composer->expects(static::atLeastOnce())
            ->method('getLocker')
            ->willReturn($locker)
        ;

        $config = $this->getMockBuilder('Composer\Config')->disableOriginalConstructor()->setMethods(array('get'))->getMock();
        $this->composer->expects(static::atLeastOnce())
            ->method('getConfig')
            ->willReturn($config)
        ;

        $config->expects(static::atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($key, $default = null) use ($vendorDir) {
                return 'vendor-dir' === $key ? $vendorDir : $default;
            })
        ;

        if (0 === \count($packages)) {
            $this->fs->expects(static::once())
                ->method('remove')
                ->with($vendorDir)
            ;
        } else {
            $this->fs->expects(static::never())
                ->method('remove')
            ;

            $this->installer->expects(static::once())
                ->method('run')
            ;
        }

        $this->composerFallback->save();
        $this->composerFallback->restore();
    }
}
