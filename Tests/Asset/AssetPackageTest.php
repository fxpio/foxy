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

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;
use Foxy\Asset\AssetPackage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Asset package tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AssetPackageTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface
     */
    protected $rootPackage;

    /**
     * @var JsonFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonFile;

    protected function setUp()
    {
        parent::setUp();

        $this->cwd = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('foxy_asset_package_test_', true);
        $this->sfs = new Filesystem();
        $this->rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $this->jsonFile = $this->getMockBuilder('Composer\Json\JsonFile')->disableOriginalConstructor()
            ->setMethods(array('exists', 'getPath', 'read', 'write'))
            ->getMock()
        ;

        $this->rootPackage->expects(static::any())
            ->method('getLicense')
            ->willReturn(array())
        ;

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

        static::assertSame($package, $assetPackage->getPackage());
    }

    public function testWrite()
    {
        $package = array(
            'name' => '@foo/bar',
        );

        $this->jsonFile->expects(static::once())
            ->method('exists')
            ->willReturn(false)
        ;

        $this->jsonFile->expects(static::once())
            ->method('write')
            ->with($package)
        ;

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
     * @param string $license
     */
    public function testInjectionOfRequiredKeys(array $expected, array $package, $license)
    {
        $this->addPackageFile($package);

        $this->rootPackage = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
        $this->rootPackage->expects(static::any())
            ->method('getLicense')
            ->willReturn(array($license))
        ;

        $assetPackage = new AssetPackage($this->rootPackage, $this->jsonFile);

        static::assertEquals($expected, $assetPackage->getPackage());
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

        static::assertEquals($expected, $assetPackage->getInstalledDependencies());
    }

    public function testAddNewDependencies()
    {
        $expected = array(
            'dependencies' => array(
                '@bar/foo' => '^1.0.0',
                '@composer-asset/baz--bar' => 'file:./path/baz/bar',
                '@composer-asset/foo--bar' => 'file:./path/foo/bar',
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

        static::assertSame($expected, $assetPackage->getPackage());
        static::assertSame($expectedExisting, $existing);
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

        static::assertEquals($expected, $assetPackage->getPackage());
    }

    /**
     * Add the package in file.
     *
     * @param array       $package       The package
     * @param null|string $contentString The string content of package
     */
    protected function addPackageFile(array $package, $contentString = null)
    {
        $filename = $this->cwd.'/package.json';
        $contentString = null !== $contentString ? $contentString : json_encode($package);

        $this->jsonFile->expects(static::any())
            ->method('exists')
            ->willReturn(true)
        ;

        $this->jsonFile->expects(static::any())
            ->method('getPath')
            ->willReturn($filename)
        ;

        $this->jsonFile->expects(static::any())
            ->method('read')
            ->willReturn($package)
        ;

        file_put_contents($filename, $contentString);
    }
}
