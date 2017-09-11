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
use Composer\IO\IOInterface;
use Foxy\Config\Config;
use Foxy\Fallback\ComposerFallback;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests for composer fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerFallbackTest extends \PHPUnit_Framework_TestCase
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
     * @var Filesystem
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
        $this->cwd = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foxy_composer_fallback_test_'.uniqid();
        $this->config = new Config(array(
            'fallback-composer' => true,
        ));
        $this->composer = $this->getMockBuilder('Composer\Composer')->disableOriginalConstructor()->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->sfs = new Filesystem();
        $this->sfs->mkdir($this->cwd);
        chdir($this->cwd);

        $this->composerFallback = new ComposerFallback($this->composer, $this->io, $this->config);
    }

    protected function tearDown()
    {
        parent::tearDown();

        chdir($this->oldCwd);
        $this->sfs->remove($this->cwd);
        $this->config = null;
        $this->composer = null;
        $this->io = null;
        $this->sfs = null;
        $this->composerFallback = null;
        $this->oldCwd = null;
        $this->cwd = null;
    }

    public function testRunWithDisableOption()
    {
        $config = new Config(array(
            'fallback-composer' => false,
        ));
        $composerFallback = new ComposerFallback($this->composer, $this->io, $config);

        $this->io->expects($this->never())
            ->method('write');

        $composerFallback->restore();
    }

    /**
     * @expectedException \Foxy\Exception\RuntimeException
     * @expectedExceptionMessage The fallback for the Composer lock file and its dependencies is not implemented currently
     */
    public function testRun()
    {
        $this->composerFallback->restore();
    }
}
