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

use Foxy\Asset\NpmManager;

/**
 * NPM asset manager tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class NpmAssetManagerTest extends AbstractAssetManagerTest
{

    public function getRunData()
    {
        return array(
            "install" => array(0, 'install', true),
            "install prod" => array(0, 'install --prod', null),
            "update dev" => array(0, 'update --dev', true),
            "update" => array(0, 'update', null),
            "install fallback" => array(1, 'install', true),
            "update fallback" => array(1, 'update', null),
        );
    }

    public function testIsValidForUpdate()
    {
        $manager = $this->getManager();
        self::assertTrue($manager->isValidForUpdate());
    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return new NpmManager($this->io, $this->config, $this->executor, $this->fs, $this->fallback);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMockedManager($mockedMethods)
    {
        $mockedManger = $this->getMockBuilder('Foxy\Asset\NpmManager')
            ->setConstructorArgs(array($this->io, $this->config, $this->executor, $this->fs, $this->fallback))
            ->setMethods($mockedMethods)
            ->getMock();

        return $mockedManger;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidName()
    {
        return 'npm';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidLockPackageName()
    {
        return 'package-lock.json';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidVersionCommand()
    {
        return 'npm --version';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidInstallCommand($isDevMode)
    {
        $additionalOptions = "";
        if($isDevMode !== true){
            $additionalOptions = ' --prod';
        }

        return 'npm install' . $additionalOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidUpdateCommand($isDevMode)
    {
        $additionalOptions = "";
        if($isDevMode === true){
            $additionalOptions = ' --dev';
        }
        return 'npm update' . $additionalOptions;
    }
}
