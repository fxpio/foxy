<?php

/**
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Asset;

use Foxy\Asset\YarnManager;

/**
 * Yarn Next asset manager tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class YarnNextAssetManagerTest extends AbstractAssetManagerTest
{
    /**
     * {@inheritdoc}
     */
    public function actionForTestRunForInstallCommand($action)
    {
        $this->executor->addExpectedValues(0, '2.0.0');

        if ('update' === $action) {
            $this->executor->addExpectedValues(0, '2.0.0');
        }
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
    protected function getValidInstallCommand()
    {
        return 'yarn install';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidUpdateCommand()
    {
        return 'yarn up';
    }

    /**
     * {@inheritdoc}
     */
    protected function actionForTestAddDependenciesForUpdateCommand()
    {
        $this->executor->addExpectedValues(0, '2.0.0');
        $this->executor->addExpectedValues(0, 'CHECK OUTPUT');
    }
}
