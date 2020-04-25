<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Solver;

use Composer\Composer;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\HttpDownloader;
use Foxy\Asset\AssetManagerInterface;
use Foxy\Config\Config;
use Foxy\Fallback\FallbackInterface;
use Foxy\Solver\Solver;
use Foxy\Solver\SolverInterface;

/**
 * Tests for solver.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SolverTest extends \PHPUnit\Framework\TestCase
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
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composerConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WritableRepositoryInterface
     */
    protected $localRepo;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fs;

    /**
     * @var InstallationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $im;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $sfs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface
     */
    protected $package;

    /**
     * @var AssetManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var FallbackInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composerFallback;

    /**
     * @var string
     */
    protected $oldCwd;

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var SolverInterface
     */
    protected $solver;

    protected function setUp()
    {
        parent::setUp();

        $this->oldCwd = getcwd();
        $this->cwd = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_solver_test_', true);
        $this->config = new Config(array(
            'enabled' => true,
            'composer-asset-dir' => $this->cwd.'/composer-asset-dir',
        ));
        $this->composer = $this->getMockBuilder('Composer\Composer')->disableOriginalConstructor()->getMock();
        $this->composerConfig = $this->getMockBuilder('Composer\Config')->disableOriginalConstructor()->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->fs = $this->getMockBuilder('Composer\Util\Filesystem')->disableOriginalConstructor()->getMock();
        $this->im = $this->getMockBuilder('Composer\Installer\InstallationManager')->disableOriginalConstructor()
            ->setMethods(array('getInstallPath'))->getMock();
        $this->sfs = new \Symfony\Component\Filesystem\Filesystem();
        $this->package = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $this->manager = $this->getMockBuilder('Foxy\Asset\AssetManagerInterface')->getMock();
        $this->composerFallback = $this->getMockBuilder('Foxy\Fallback\FallbackInterface')->getMock();
        $this->sfs->mkdir($this->cwd);
        chdir($this->cwd);

        $this->localRepo = $this->getMockBuilder('Composer\Repository\InstalledArrayRepository')
            ->setMethods(array('getCanonicalPackages'))
            ->getMock()
        ;

        if (class_exists('Composer\Util\HttpDownloader')) {
            $rm = new RepositoryManager($this->io, $this->composerConfig, new HttpDownloader($this->io, $this->composerConfig));
            $rm->setLocalRepository($this->localRepo);
        } else {
            $rm = new RepositoryManager($this->io, $this->composerConfig);
            $rm->setLocalRepository($this->localRepo);
        }

        $this->composer->expects(static::any())
            ->method('getRepositoryManager')
            ->willReturn($rm)
        ;

        $this->composer->expects(static::any())
            ->method('getInstallationManager')
            ->willReturn($this->im)
        ;

        $this->composer->expects(static::any())
            ->method('getPackage')
            ->willReturn($this->package)
        ;

        $this->composer->expects(static::any())
            ->method('getConfig')
            ->willReturn($this->composerConfig)
        ;

        $this->composer->expects(static::any())
            ->method('getEventDispatcher')
            ->willReturn(new EventDispatcher($this->composer, $this->io))
        ;

        $sfs = $this->sfs;
        $this->fs->expects(static::any())
            ->method('findShortestPath')
            ->willReturnCallback(function ($from, $to) use ($sfs) {
                return rtrim($sfs->makePathRelative($to, $from), '/');
            })
        ;

        $this->solver = new Solver($this->manager, $this->config, $this->fs, $this->composerFallback);
    }

    protected function tearDown()
    {
        parent::tearDown();

        chdir($this->oldCwd);
        $this->sfs->remove($this->cwd);
        $this->config = null;
        $this->composer = null;
        $this->composerConfig = null;
        $this->localRepo = null;
        $this->io = null;
        $this->fs = null;
        $this->im = null;
        $this->sfs = null;
        $this->package = null;
        $this->manager = null;
        $this->composerFallback = null;
        $this->solver = null;
        $this->oldCwd = null;
        $this->cwd = null;
    }

    public function testSetUpdatable()
    {
        $this->manager->expects(static::once())
            ->method('setUpdatable')
            ->with(false)
        ;

        $this->solver->setUpdatable(false);
    }

    public function testSolveWithDisableOption()
    {
        $config = new Config(array(
            'enabled' => false,
        ));
        $solver = new Solver($this->manager, $config, $this->fs);

        $this->manager->expects(static::never())
            ->method('run')
        ;

        $solver->solve($this->composer, $this->io);
    }

    public function getSolveData()
    {
        return array(
            array(0),
            array(1),
        );
    }

    /**
     * @dataProvider getSolveData
     *
     * @param int $resRunManager The result value of the run command of asset manager
     */
    public function testSolve($resRunManager)
    {
        /** @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $requirePackage */
        $requirePackage = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $requirePackage->expects(static::any())
            ->method('getName')
            ->willReturn('foo/bar')
        ;
        $requirePackage->expects(static::any())
            ->method('getRequires')
            ->willReturn(array(
                new Link('root/package', 'foxy/foxy'),
            ))
        ;
        $requirePackage->expects(static::any())
            ->method('getDevRequires')
            ->willReturn(array())
        ;

        $this->addInstalledPackages(array(
            $requirePackage,
        ));

        $requirePackagePath = $this->cwd.'/vendor/foo/bar';

        $this->im->expects(static::once())
            ->method('getInstallPath')
            ->willReturn($requirePackagePath)
        ;

        $this->manager->expects(static::exactly(2))
            ->method('getPackageName')
            ->willReturn('package.json')
        ;

        $this->manager->expects(static::once())
            ->method('addDependencies')
        ;

        $this->manager->expects(static::once())
            ->method('run')
            ->willReturn($resRunManager)
        ;

        if (0 === $resRunManager) {
            $this->composerFallback->expects(static::never())
                ->method('restore')
            ;
        } else {
            $this->composerFallback->expects(static::once())
                ->method('restore')
            ;

            $this->expectException('RuntimeException');
            $this->expectExceptionMessage('The asset manager ended with an error');
        }

        $requirePackageFilename = $requirePackagePath.\DIRECTORY_SEPARATOR.$this->manager->getPackageName();
        $this->sfs->mkdir(\dirname($requirePackageFilename));
        file_put_contents($requirePackageFilename, '{}');

        $this->solver->solve($this->composer, $this->io);
    }

    /**
     * Add the installed packages in local repository.
     *
     * @param PackageInterface[] $packages The installed packages
     */
    protected function addInstalledPackages(array $packages = array())
    {
        $this->localRepo->expects(static::any())
            ->method('getCanonicalPackages')
            ->willReturn($packages)
        ;
    }
}
