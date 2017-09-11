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
 */
class YarnAssetManagerTest extends AbstractAssetManagerTest
{
    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return new YarnManager($this->config, $this->executor, $this->fs);
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
}
