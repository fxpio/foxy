<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\AssetPackage;

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Foxy\AssetPackage\AssetPackage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Asset package tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetPackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var Filesystem
     */
    protected $sfs;

    /**
     * @var RootPackageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootPackage;

    /**
     * @var JsonFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonFile;

    protected function setUp()
    {
        parent::setUp();

        $this->cwd = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foxy_asset_package_test_'.uniqid();
        $this->sfs = new Filesystem();
        $this->rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $this->jsonFile = $this->getMockBuilder('Composer\Json\JsonFile')->disableOriginalConstructor()
            ->setMethods(array('exists', 'getPath', 'read', 'write'))
            ->getMock();

        $this->sfs->mkdir($this->cwd);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->sfs->remove($this->cwd);
        $this->jsonFile = null;
        $this->rootPackage = null;
        $this->sfs = null;
        $this->cwd = null;
    }

    public function testGetPackageWithExistingFile()
    {
        $package = array(
            'name' => '@foo/bar',
        );
        $contentString = json_encode($package);
        $this->addPackageFile($package, $contentString);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);

        $this->assertSame($package, $assetPackage->getPackage());
        $this->assertSame($contentString, $assetPackage->getOriginalContent());
    }

    public function testWrite()
    {
        $package = array(
            'name' => '@foo/bar',
        );

        $this->jsonFile->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->jsonFile->expects($this->once())
            ->method('write')
            ->with($package);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);
        $assetPackage->setPackage($package);
        $assetPackage->write();
    }

    public function getDataRequiredKeys()
    {
        return array(
            array(
                array(
                    'name' => '@foo/bar',
                    'license' => 'MIT',
                ),
                array(
                    'name' => '@foo/bar',
                    'license' => 'MIT',
                ),
                'proprietary',
            ),
            array(
                array(
                    'name' => '@foo/bar',
                    'license' => 'MIT',
                ),
                array(
                    'name' => '@foo/bar',
                ),
                'MIT',
            ),
            array(
                array(
                    'name' => '@foo/bar',
                    'private' => true,
                ),
                array(
                    'name' => '@foo/bar',
                ),
                'proprietary',
            ),
        );
    }

    /**
     * @dataProvider getDataRequiredKeys
     *
     * @param array  $expected
     * @param array  $package
     * @param string $license
     */
    public function testInjectionOfRequiredKeys(array $expected, array $package, $license)
    {
        $this->addPackageFile($package);

        $this->rootPackage->expects($this->any())
            ->method('getLicense')
            ->willReturn(array($license));

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);

        $this->assertEquals($expected, $assetPackage->getPackage());
    }

    public function testGetInstalledDependencies()
    {
        $expected = array(
            '@composer-asset/foo--bar' => 'file:./path/foo/bar',
            '@composer-asset/baz--bar' => 'file:./path/baz/bar',
        );
        $package = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $this->addPackageFile($package);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);

        $this->assertEquals($expected, $assetPackage->getInstalledDependencies());
    }

    public function testAddNewDependencies()
    {
        $expected = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
                '@composer-asset/new--dependency' => 'file:./path/new/dependency',
            ),
        );
        $expectedExisting = array(
            '@composer-asset/foo--bar',
            '@composer-asset/baz--bar',
        );

        $package = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $dependencies = array(
            '@composer-asset/foo--bar' => 'path/foo/bar/package.json',
            '@composer-asset/baz--bar' => 'path/baz/bar/package.json',
            '@composer-asset/new--dependency' => 'path/new/dependency/package.json',
        );
        $this->addPackageFile($package);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);
        $existing = $assetPackage->addNewDependencies($dependencies);

        $this->assertEquals($expected, $assetPackage->getPackage());
        $this->assertEquals($expectedExisting, $existing);
    }

    public function testRemoveUnusedDependencies()
    {
        $expected = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
            ),
        );

        $package = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $dependencies = array(
            '@composer-asset/foo--bar' => 'file:./path/foo/bar',
        );
        $this->addPackageFile($package);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);
        $assetPackage->removeUnusedDependencies($dependencies);

        $this->assertEquals($expected, $assetPackage->getPackage());
    }

    public function testRestoreWithPackageFile()
    {
        $filename = $this->cwd.'/package.json';
        $package = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $contentString = json_encode($package);
        $this->addPackageFile($package);
        $this->assertFileExists($filename);
        $this->assertSame($contentString, file_get_contents($filename));

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);
        $newPackage = $package;
        unset($newPackage['dependencies']['@bar/foo']);
        $assetPackage->setPackage($newPackage);

        $this->jsonFile->expects($this->any())
            ->method('write')
            ->with($newPackage)
            ->willReturnCallback(function ($value) use ($filename) {
                $contentStringNew = json_encode($value);
                file_put_contents($filename, $contentStringNew);
                $this->assertFileExists($filename);
                $this->assertSame($contentStringNew, file_get_contents($filename));
            });

        $assetPackage->write();
        $this->assertNotSame($contentString, file_get_contents($filename));

        $assetPackage->restore();
        $this->assertFileExists($filename);
        $this->assertSame($contentString, file_get_contents($filename));
    }

    public function testRestoreWithoutPackageFile()
    {
        $filename = $this->cwd.'/package.json';
        $newPackage = array(
            'dependencies' => array(
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
            ),
        );
        $this->assertFileNotExists($filename);

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);
        $assetPackage->setPackage($newPackage);

        $this->jsonFile->expects($this->any())
            ->method('exists')
            ->willReturn(false);

        $this->jsonFile->expects($this->any())
            ->method('getPath')
            ->willReturn($filename);

        $this->jsonFile->expects($this->any())
            ->method('write')
            ->with($newPackage)
            ->willReturnCallback(function ($value) use ($filename) {
                $contentStringNew = json_encode($value);
                file_put_contents($filename, $contentStringNew);
                $this->assertFileExists($filename);
                $this->assertSame($contentStringNew, file_get_contents($filename));
            });

        $assetPackage->write();

        $assetPackage->restore();
        $this->assertFileNotExists($filename);
    }

    /**
     * Add the package in file.
     *
     * @param array       $package       The package
     * @param string|null $contentString The string content of package
     */
    protected function addPackageFile(array $package, $contentString = null)
    {
        $filename = $this->cwd.'/package.json';
        $contentString = null !== $contentString ? $contentString : json_encode($package);

        $this->jsonFile->expects($this->any())
            ->method('exists')
            ->willReturn(true);

        $this->jsonFile->expects($this->any())
            ->method('getPath')
            ->willReturn($filename);

        $this->jsonFile->expects($this->any())
            ->method('read')
            ->willReturn($package);

        file_put_contents($filename, $contentString);
    }
}
