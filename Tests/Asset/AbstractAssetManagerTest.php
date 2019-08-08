<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Asset;

use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Composer\Util\Filesystem;
use Foxy\Asset\AbstractAssetManager;
use Foxy\Asset\AssetManagerInterface;
use Foxy\Config\Config;
use Foxy\Fallback\FallbackInterface;
use Foxy\Tests\Fixtures\Util\ProcessExecutorMock;

/**
 * Abstract class for asset manager tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractAssetManagerTest extends \PHPUnit\Framework\TestCase
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
     * @var FallbackInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fallback;

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
        $this->fallback = $this->getMockBuilder('Foxy\Fallback\FallbackInterface')->getMock();
        $this->manager = $this->getManager();
        $this->oldCwd = getcwd();
        $this->cwd = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_asset_manager_test_', true);
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
        $this->fallback = null;
        $this->manager = null;
        $this->oldCwd = null;
        $this->cwd = null;
    }

    public function testGetName()
    {
        static::assertSame($this->getValidName(), $this->manager->getName());
    }

    public function testGetLockPackageName()
    {
        static::assertSame($this->getValidLockPackageName(), $this->manager->getLockPackageName());
    }

    public function testGetPackageName()
    {
        static::assertSame('package.json', $this->manager->getPackageName());
    }

    public function testHasLockFile()
    {
        static::assertFalse($this->manager->hasLockFile());
    }

    public function testIsInstalled()
    {
        static::assertFalse($this->manager->isInstalled());
    }

    public function testIsUpdatable()
    {
        static::assertFalse($this->manager->isUpdatable());
    }

    public function testSetUpdatable()
    {
        $res = $this->manager->setUpdatable(false);
        static::assertInstanceOf('Foxy\Asset\AssetManagerInterface', $res);
    }

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

        $this->executor->addExpectedValues(0, '42.0.0');

        $this->manager->validate();
    }

    public function testValidateWithInstalledManagerAndWithValidVersion()
    {
        $this->config = new Config(array(), array(
            'manager-version' => '>=41.0',
        ));
        $this->manager = $this->getManager();

        $this->executor->addExpectedValues(0, '42.0.0');

        $this->manager->validate();
        static::assertSame('>=41.0', $this->config->get('manager-version'));
    }

    public function testValidateWithInstalledManagerAndWithoutValidationVersion()
    {
        $this->executor->addExpectedValues(0, '42.0.0');

        $this->manager->validate();
        static::assertNull($this->config->get('manager-version'));
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface $rootPackage */
        $rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $rootPackage->expects(static::any())
            ->method('getLicense')
            ->willReturn(array())
        ;

        static::assertFalse($this->manager->isInstalled());
        static::assertFalse($this->manager->isUpdatable());

        $assetPackage = $this->manager->addDependencies($rootPackage, $allDependencies);
        static::assertInstanceOf('Foxy\Asset\AssetPackageInterface', $assetPackage);

        static::assertEquals($expectedPackage, $assetPackage->getPackage());
    }

    public function testAddDependenciesForUpdateCommand()
    {
        $this->actionForTestAddDependenciesForUpdateCommand();

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
        /** @var \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface $rootPackage */
        $rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $rootPackage->expects(static::any())
            ->method('getLicense')
            ->willReturn(array())
        ;
        $nodeModulePath = $this->cwd.ltrim(AbstractAssetManager::NODE_MODULES_PATH, '.');

        $jsonFile->write($package);
        static::assertFileExists($jsonFile->getPath());
        $this->sfs->mkdir($nodeModulePath);
        static::assertFileExists($nodeModulePath);
        $lockFilePath = $this->cwd.\DIRECTORY_SEPARATOR.$this->manager->getLockPackageName();
        file_put_contents($lockFilePath, '{}');
        static::assertFileExists($lockFilePath);
        static::assertTrue($this->manager->isInstalled());
        static::assertTrue($this->manager->isUpdatable());

        $assetPackage = $this->manager->addDependencies($rootPackage, $allDependencies);
        static::assertInstanceOf('Foxy\Asset\AssetPackageInterface', $assetPackage);

        static::assertEquals($expectedPackage, $assetPackage->getPackage());
    }

    public function testRunWithDisableOption()
    {
        $this->config = new Config(array(), array(
            'run-asset-manager' => false,
        ));

        static::assertSame(0, $this->getManager()->run());
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
        $this->actionForTestRunForInstallCommand($action);

        $this->config = new Config(array(), array(
            'run-asset-manager' => true,
            'fallback-asset' => true,
        ));
        $this->manager = $this->getManager();

        if ('install' === $action) {
            $expectedCommand = $this->getValidInstallCommand();
        } else {
            $expectedCommand = $this->getValidUpdateCommand();
            file_put_contents($this->cwd.\DIRECTORY_SEPARATOR.$this->manager->getPackageName(), '{}');
            $nodeModulePath = $this->cwd.ltrim(AbstractAssetManager::NODE_MODULES_PATH, '.');
            $this->sfs->mkdir($nodeModulePath);
            static::assertFileExists($nodeModulePath);
            $lockFilePath = $this->cwd.\DIRECTORY_SEPARATOR.$this->manager->getLockPackageName();
            file_put_contents($lockFilePath, '{}');
            static::assertFileExists($lockFilePath);
            static::assertTrue($this->manager->isInstalled());
            static::assertTrue($this->manager->isUpdatable());
        }

        if (0 === $expectedRes) {
            $this->fallback->expects(static::never())
                ->method('restore')
            ;
        } else {
            $this->fallback->expects(static::once())
                ->method('restore')
            ;
        }

        $this->executor->addExpectedValues($expectedRes, 'ASSET MANAGER OUTPUT');

        static::assertSame($expectedRes, $this->getManager()->run());
        static::assertSame($expectedCommand, $this->executor->getLastCommand());
        static::assertSame('ASSET MANAGER OUTPUT', $this->executor->getLastOutput());
    }

    /**
     * @return AssetManagerInterface
     */
    abstract protected function getManager();

    /**
     * @return string
     */
    abstract protected function getValidName();

    /**
     * @return string
     */
    abstract protected function getValidLockPackageName();

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

    protected function actionForTestAddDependenciesForUpdateCommand()
    {
        // do nothing by default
    }

    /**
     * @param string $action The action
     */
    protected function actionForTestRunForInstallCommand($action)
    {
        // do nothing by default
    }
}
