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

use Composer\Config;
use Composer\IO\ConsoleIO;
use Composer\IO\IOInterface;
use Foxy\Util\ConsoleUtil;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests for console util.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ConsoleUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testGetInput()
    {
        $input = new ArgvInput();
        $output = new NullOutput();
        $helperSet = new HelperSet();
        $io = new ConsoleIO($input, $output, $helperSet);

        static::assertSame($input, ConsoleUtil::getInput($io));
    }

    public function testGetInputWithoutValidInput()
    {
        /** @var IOInterface $io */
        $io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();

        static::assertInstanceOf('Symfony\Component\Console\Input\ArgvInput', ConsoleUtil::getInput($io));
    }

    public function getPreferredInstallOptionsData()
    {
        return array(
            array(false, false, 'auto',   false),
            array(false, true,  'auto',   true),
            array(true,  false, 'source', false),
            array(false, true,  'dist',   false),
        );
    }

    /**
     * @dataProvider getPreferredInstallOptionsData
     *
     * @param bool   $expectedPreferSource
     * @param bool   $expectedPreferDist
     * @param string $preferedInstall
     * @param bool   $inputPrefer
     */
    public function testGetPreferredInstallOptions($expectedPreferSource, $expectedPreferDist, $preferedInstall, $inputPrefer)
    {
        /** @var Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()
            ->setMethods(array('get'))->getMock();
        /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject $input */
        $input = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')->getMock();

        $config->expects(static::once())
            ->method('get')
            ->with('preferred-install')
            ->willReturn($preferedInstall)
        ;

        if ($inputPrefer) {
            $input->expects(static::at(0))
                ->method('getOption')
                ->with('prefer-source')
                ->willReturn(false)
            ;

            $input->expects(static::at(1))
                ->method('getOption')
                ->with('prefer-dist')
                ->willReturn(true)
            ;

            $input->expects(static::at(2))
                ->method('getOption')
                ->with('prefer-source')
                ->willReturn(false)
            ;

            $input->expects(static::at(3))
                ->method('getOption')
                ->with('prefer-dist')
                ->willReturn(true)
            ;
        }

        $res = ConsoleUtil::getPreferredInstallOptions($config, $input);

        static::assertEquals(array($expectedPreferSource, $expectedPreferDist), $res);
    }
}
