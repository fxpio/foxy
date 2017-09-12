<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Util;

use Composer\Installer\InstallationManager;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Foxy\Asset\AbstractAssetManager;
use Foxy\Util\AssetUtil;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests for asset util.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $sfs;

    /**
     * @var string
     */
    protected $cwd;

    protected function setUp()
    {
        parent::setUp();

        $this->cwd = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foxy_asset_util_test_'.uniqid();
        $this->sfs = new Filesystem();
        $this->sfs->mkdir($this->cwd);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->sfs->remove($this->cwd);
        $this->sfs = null;
        $this->cwd = null;
    }

    public function testGetName()
    {
        /* @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->expects($this->once())
            ->method('getName')
            ->willReturn('foo/bar');

        $this->assertSame('@composer-asset/foo--bar', AssetUtil::getName($package));
    }

    public function testGetPathWithoutRequiredFoxy()
    {
        /* @var InstallationManager|\PHPUnit_Framework_MockObject_MockObject $installationManager */
        $installationManager = $this->getMockBuilder('Composer\Installer\InstallationManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getInstallPath'))
            ->getMock();
        $installationManager->expects($this->never())
            ->method('getInstallPath');

        /* @var AbstractAssetManager|\PHPUnit_Framework_MockObject_MockObject $assetManager */
        $assetManager = $this->getMockBuilder('Foxy\Asset\AbstractAssetManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /* @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->expects($this->once())
            ->method('getRequires')
            ->willReturn(array());
        $package->expects($this->once())
            ->method('getDevRequires')
            ->willReturn(array());

        $res = AssetUtil::getPath($installationManager, $assetManager, $package);

        $this->assertNull($res);
    }

    public function getRequiresData()
    {
        return array(
            array(array(new Link('root/package', 'foxy/foxy')), array(), false),
            array(array(), array(new Link('root/package', 'foxy/foxy')), false),
            array(array(new Link('root/package', 'foxy/foxy')), array(), true),
            array(array(), array(new Link('root/package', 'foxy/foxy')), true),
        );
    }

    /**
     * @dataProvider getRequiresData
     *
     * @param Link[] $requires
     * @param Link[] $devRequires
     * @param bool   $fileExists
     */
    public function testGetPathWithRequiredFoxy(array $requires, array $devRequires, $fileExists = false)
    {
        /* @var InstallationManager|\PHPUnit_Framework_MockObject_MockObject $installationManager */
        $installationManager = $this->getMockBuilder('Composer\Installer\InstallationManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getInstallPath'))
            ->getMock();
        $installationManager->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->cwd);

        /* @var AbstractAssetManager|\PHPUnit_Framework_MockObject_MockObject $assetManager */
        $assetManager = $this->getMockBuilder('Foxy\Asset\AbstractAssetManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /* @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->expects($this->once())
            ->method('getRequires')
            ->willReturn($requires);

        if (0 === count($devRequires)) {
            $package->expects($this->never())
                ->method('getDevRequires');
        } else {
            $package->expects($this->once())
                ->method('getDevRequires')
                ->willReturn($devRequires);
        }

        if ($fileExists) {
            $expectedFilename = $this->cwd.DIRECTORY_SEPARATOR.$assetManager->getPackageName();
            file_put_contents($expectedFilename, '{}');
            $expectedFilename = str_replace('\\', '/', realpath($expectedFilename));
        } else {
            $expectedFilename = null;
        }

        $res = AssetUtil::getPath($installationManager, $assetManager, $package);

        $this->assertSame($expectedFilename, $res);
    }

    public function getExtraData()
    {
        return array(
            array(false, false),
            array(true,  false),
            array(false, true),
            array(true,  true),
        );
    }

    /**
     * @dataProvider getExtraData
     *
     * @param bool $withExtra
     * @param bool $fileExists
     */
    public function testGetPathWithExtraActivation($withExtra, $fileExists = false)
    {
        /* @var InstallationManager|\PHPUnit_Framework_MockObject_MockObject $installationManager */
        $installationManager = $this->getMockBuilder('Composer\Installer\InstallationManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getInstallPath'))
            ->getMock();

        if ($withExtra && $fileExists) {
            $installationManager->expects($this->once())
                ->method('getInstallPath')
                ->willReturn($this->cwd);
        }

        /* @var AbstractAssetManager|\PHPUnit_Framework_MockObject_MockObject $assetManager */
        $assetManager = $this->getMockBuilder('Foxy\Asset\AbstractAssetManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /* @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->expects($this->any())
            ->method('getRequires')
            ->willReturn(array());

        $package->expects($this->any())
            ->method('getDevRequires')
            ->willReturn(array());

        $package->expects($this->atLeastOnce())
            ->method('getExtra')
            ->willReturn(array(
                'foxy' => $withExtra,
            ));

        if ($fileExists) {
            $expectedFilename = $this->cwd.DIRECTORY_SEPARATOR.$assetManager->getPackageName();
            file_put_contents($expectedFilename, '{}');
            $expectedFilename = $withExtra ? str_replace('\\', '/', realpath($expectedFilename)) : null;
        } else {
            $expectedFilename = null;
        }

        $res = AssetUtil::getPath($installationManager, $assetManager, $package);

        $this->assertSame($expectedFilename, $res);
    }

    public function testHasNoPluginDependency()
    {
        $this->assertFalse(AssetUtil::hasPluginDependency(array(
            new Link('root/package', 'foo/bar'),
        )));
    }

    public function testHasPluginDependency()
    {
        $this->assertTrue(AssetUtil::hasPluginDependency(array(
            new Link('root/package', 'foo/bar'),
            new Link('root/package', 'foxy/foxy'),
            new Link('root/package', 'bar/foo'),
        )));
    }

    public function getFormatPackageData()
    {
        return array(
            array('1.0.0', null, '1.0.0'),
            array('1.0.1', '1.0.0', '1.0.0'),
            array('dev-master', null, '1.0.0', '1-dev'),
            array('dev-master', null, '1.0.0', '1.0-dev'),
            array('dev-master', null, '1.0.0', '1.0.0-dev'),
            array('dev-master', null, '1.0.0', '1.x-dev'),
            array('dev-master', null, '1.0.0', '1.0.x-dev'),
            array('dev-master', null, '1.0.0', '1.*-dev'),
            array('dev-master', null, '1.0.0', '1.0.*-dev'),
        );
    }

    /**
     * @dataProvider getFormatPackageData
     *
     * @param string      $packageVersion
     * @param string|null $assetVersion
     * @param string      $expectedAssetVersion
     * @param string|null $branchAlias
     */
    public function testFormatPackage($packageVersion, $assetVersion, $expectedAssetVersion, $branchAlias = null)
    {
        $packageName = '@composer-asset/foo--bar';
        /* @var PackageInterface|\PHPUnit_Framework_MockObject_MockObject $package */
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();

        $assetPackage = array();

        if (null !== $assetVersion) {
            $assetPackage['version'] = $assetVersion;

            $package->expects($this->never())
                ->method('getPrettyVersion');
            $package->expects($this->never())
                ->method('getExtra');
        } else {
            $extra = array();

            if (null !== $branchAlias) {
                $extra['branch-alias'][$packageVersion] = $branchAlias;
            }

            $package->expects($this->once())
                ->method('getPrettyVersion')
                ->willReturn($packageVersion);
            $package->expects($this->once())
                ->method('getExtra')
                ->willReturn($extra);
        }

        $expected = array(
            'name' => $packageName,
            'version' => $expectedAssetVersion,
        );

        $res = AssetUtil::formatPackage($package, $packageName, $assetPackage);

        $this->assertEquals($expected, $res);
    }
}
