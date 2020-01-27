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

use Foxy\Asset\YarnManager;

/**
 * Yarn asset manager tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class YarnAssetManagerTest extends AbstractAssetManagerTest
{
    public function getRunData()
    {
        return array(
            "install" => array(0, 'install --non-interactive', true),
            "install prod" => array(0, 'install --non-interactive --prod', null),
            "update dev" => array(0, 'upgrade --non-interactive', true),
            "update" => array(0, 'upgrade --non-interactive --prod', null),
            "install fallback" => array(1, 'install --non-interactive', true),
            "update fallback" => array(1, 'upgrade --non-interactive --prod', null),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function actionForTestRunForInstallCommand($action)
    {
        if ('update' === $action) {
            $this->executor->addExpectedValues(0, 'CHECK OUTPUT');
            $this->executor->addExpectedValues(0, 'CHECK OUTPUT');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateDataProvider()
    {
        return array(
            'update true' => array(0, true),
            'update false' => array(1, false)
        );
    }

    /**
     * @dataProvider getUpdateDataProvider
     */
    public function testIsValidForUpdateReturnsTrueOnSuccessfullCheck($returnCode, $expectedResult)
    {
        $manager = $this->getManager();
        $this->executor->addExpectedValues($returnCode);

        self::assertSame($expectedResult, $manager->isValidForUpdate());
        self::assertSame('yarn check --non-interactive', $this->executor->getLastCommand());

    }

    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return new YarnManager($this->io, $this->config, $this->executor, $this->fs, $this->fallback);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMockedManager($mockedMethods)
    {
        $mockedManger = $this->getMockBuilder('Foxy\Asset\YarnManager')
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
        return 'yarn';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidLockPackageName()
    {
        return 'yarn.lock';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidVersionCommand()
    {
        return 'yarn --version';
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

        return 'yarn install --non-interactive' . $additionalOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidUpdateCommand($isDevMode)
    {
        $additionalOptions = "";
        if($isDevMode !== true){
            $additionalOptions = ' --prod';
        }
        return 'yarn upgrade --non-interactive' . $additionalOptions;
    }

    /**
     * {@inheritdoc}
     */
    protected function actionForTestAddDependenciesForUpdateCommand()
    {
        $this->executor->addExpectedValues(0, 'CHECK OUTPUT');
    }
}
