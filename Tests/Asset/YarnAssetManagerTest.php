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
        return 'yarn install --non-interactive';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidUpdateCommand()
    {
        return 'yarn upgrade --non-interactive';
    }

    /**
     * {@inheritdoc}
     */
    protected function actionForTestAddDependenciesForUpdateCommand()
    {
        $this->executor->addExpectedValues(0, 'CHECK OUTPUT');
    }
}
