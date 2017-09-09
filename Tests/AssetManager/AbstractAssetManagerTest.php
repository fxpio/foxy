<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\AssetManager;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Composer\Util\Filesystem;
use Foxy\AssetManager\AbstractAssetManager;
use Foxy\AssetManager\AssetManagerInterface;
use Foxy\AssetPackage\AssetPackageInterface;
use Foxy\Config\Config;
use Foxy\Tests\Fixtures\Util\ProcessExecutorMock;

/**
 * Abstract class for asset manager tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractAssetManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var ProcessExecutorMock
     */
    protected $executor;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fs;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $sfs;

    /**
     * @var AssetManagerInterface
     */
    protected $manager;

    /**
     * @var string
     */
    protected $oldCwd;

    /**
     * @var string
     */
    protected $cwd;

    protected function setUp()
    {
        parent::setUp();

        $this->config = new Config(array());
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->executor = new ProcessExecutorMock($this->io);
        $this->fs = $this->getMockBuilder('Composer\Util\Filesystem')->disableOriginalConstructor()->getMock();
        $this->sfs = new \Symfony\Component\Filesystem\Filesystem();
        $this->manager = $this->getManager();
        $this->oldCwd = getcwd();
        $this->cwd = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foxy_asset_manager_test_'.uniqid();
        $this->sfs->mkdir($this->cwd);
        chdir($this->cwd);
    }

    protected function tearDown()
    {
        parent::tearDown();

        chdir($this->oldCwd);
        $this->sfs->remove($this->cwd);
        $this->config = null;
        $this->io = null;
        $this->executor = null;
        $this->fs = null;
        $this->sfs = null;
        $this->manager = null;
        $this->oldCwd = null;
        $this->cwd = null;
    }

    /**
     * @return AssetManagerInterface
     */
    abstract protected function getManager();

    /**
     * @return string
     */
    abstract protected function getValidName();

    public function testGetName()
    {
        $this->assertSame($this->getValidName(), $this->manager->getName());
    }

    /**
     * @return string
     */
    abstract protected function getValidLockPackageName();

    public function testGetLockPackageName()
    {
        $this->assertSame($this->getValidLockPackageName(), $this->manager->getLockPackageName());
    }

    public function testGetPackageName()
    {
        $this->assertSame('package.json', $this->manager->getPackageName());
    }

    public function testHasLockFile()
    {
        $this->assertFalse($this->manager->hasLockFile());
    }

    public function testIsInstalled()
    {
        $this->assertFalse($this->manager->isInstalled());
    }

    /**
     * @return string
     */
    abstract protected function getValidVersionCommand();

    /**
     * @return string
     */
    abstract protected function getValidInstallCommand();

    /**
     * @return string
     */
    abstract protected function getValidUpdateCommand();

    /**
     * @expectedException \Foxy\Exception\RuntimeException
     * @expectedExceptionMessageRegExp /The binary of "(\w+)" must be installed/
     */
    public function testValidateWithoutInstalledManager()
    {
        $this->manager->validate();
    }

    /**
     * @expectedException \Foxy\Exception\RuntimeException
     * @expectedExceptionMessageRegExp /The installed (\w+) version "42.0.0" doesn't match with the constraint version ">=50.0"/
     */
    public function testValidateWithInstalledManagerAndWithoutValidVersion()
    {
        $this->config = new Config(array(), array(
            'manager-version' => '>=50.0',
        ));
        $this->manager = $this->getManager();

        $this->executor->mockExecuteOutputValue = '42.0.0';

        $this->manager->validate();
    }

    public function testValidateWithInstalledManagerAndWithValidVersion()
    {
        $this->config = new Config(array(), array(
            'manager-version' => '>=41.0',
        ));
        $this->manager = $this->getManager();

        $this->executor->mockExecuteOutputValue = '42.0.0';

        $this->manager->validate();
        $this->assertSame('>=41.0', $this->config->get('manager-version'));
    }

    public function testValidateWithInstalledManagerAndWithoutValidationVersion()
    {
        $this->executor->mockExecuteOutputValue = '42.0.0';

        $this->manager->validate();
        $this->assertNull($this->config->get('manager-version'));
    }

    public function testAddDependenciesForInstallCommand()
    {
        $expectedPackage = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@composer-asset/new--dependency' => 'file:./path/new/dependency',
            ),
        );
        $allDependencies = array(
            '@composer-asset/foo--bar' => 'path/foo/bar/package.json',
            '@composer-asset/new--dependency' => 'path/new/dependency/package.json',
        );
        /* @var RootPackageInterface|\PHPUnit_Framework_MockObject_MockObject $rootPackage */
        $rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();

        $this->assertFalse($this->manager->isInstalled());

        $assetPackage = $this->manager->addDependencies($rootPackage, $allDependencies);
        $this->assertInstanceOf('Foxy\AssetPackage\AssetPackageInterface', $assetPackage);

        $this->assertEquals($expectedPackage, $assetPackage->getPackage());
    }

    public function testAddDependenciesForUpdateCommand()
    {
        $expectedPackage = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@composer-asset/new--dependency' => 'file:./path/new/dependency',
            ),
        );
        $package = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $allDependencies = array(
            '@composer-asset/foo--bar' => 'path/foo/bar/package.json',
            '@composer-asset/new--dependency' => 'path/new/dependency/package.json',
        );
        $jsonFile = new JsonFile($this->cwd.'/package.json');
        /* @var RootPackageInterface|\PHPUnit_Framework_MockObject_MockObject $rootPackage */
        $rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $nodeModulePath = $this->cwd.ltrim(AbstractAssetManager::NODE_MODULES_PATH, '.');

        $jsonFile->write($package);
        $this->assertFileExists($jsonFile->getPath());
        $this->sfs->mkdir($nodeModulePath);
        $this->assertFileExists($nodeModulePath);
        $this->assertTrue($this->manager->isInstalled());

        $assetPackage = $this->manager->addDependencies($rootPackage, $allDependencies);
        $this->assertInstanceOf('Foxy\AssetPackage\AssetPackageInterface', $assetPackage);

        $this->assertEquals($expectedPackage, $assetPackage->getPackage());
    }

    public function testRunWithDisableOption()
    {
        $this->config = new Config(array(), array(
            'run-asset-manager' => false,
        ));
        $this->manager = $this->getManager();

        $this->assertSame(0, $this->getManager()->run());
    }

    public function getRunData()
    {
        return array(
            array(0, 'install'),
            array(0, 'update'),
            array(1, 'install'),
            array(1, 'update'),
        );
    }

    /**
     * @dataProvider getRunData
     *
     * @param int    $expectedRes
     * @param string $action
     */
    public function testRunForInstallCommand($expectedRes, $action)
    {
        $this->config = new Config(array(), array(
            'run-asset-manager' => true,
            'fallback-asset' => true,
        ));
        $this->manager = $this->getManager();
        /* @var AssetPackageInterface|\PHPUnit_Framework_MockObject_MockObject $assetPackage */
        $assetPackage = $this->getMockBuilder('Foxy\AssetPackage\AssetPackageInterface')->getMock();

        if ('install' === $action) {
            $expectedCommand = $this->getValidInstallCommand();
        } else {
            $expectedCommand = $this->getValidUpdateCommand();
            file_put_contents($this->cwd.DIRECTORY_SEPARATOR.$this->manager->getPackageName(), '{}');
            $nodeModulePath = $this->cwd.ltrim(AbstractAssetManager::NODE_MODULES_PATH, '.');
            $this->sfs->mkdir($nodeModulePath);
            $this->assertFileExists($nodeModulePath);
            $this->assertTrue($this->manager->isInstalled());
        }

        if (0 === $expectedRes) {
            $assetPackage->expects($this->never())
                ->method('restore');
        } else {
            $assetPackage->expects($this->once())
                ->method('restore');
        }

        $this->executor->mockExecuteReturnValue = $expectedRes;
        $this->executor->mockExecuteOutputValue = 'ASSET MANAGER OUTPUT';

        $this->assertSame($expectedRes, $this->getManager()->run($assetPackage));
        $this->assertSame($expectedCommand, $this->executor->getLastCommand());
        $this->assertSame('ASSET MANAGER OUTPUT', $this->executor->getLastOutput());
    }
}
