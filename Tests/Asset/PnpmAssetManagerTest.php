<?php

/*
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Asset;

use Foxy\Asset\PnpmManager;

/**
 * Pnpm asset manager tests.
 *
 * @author Steffen Dietz <steffo.dietz@gmail.com>
 *
 * @internal
 */
final class PnpmAssetManagerTest extends AbstractAssetManagerTest
{
    /**
     * {@inheritdoc}
     */
    protected function getManager()
    {
        return new PnpmManager($this->io, $this->config, $this->executor, $this->fs, $this->fallback);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidName()
    {
        return 'pnpm';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidLockPackageName()
    {
        return 'pnpm-lock.yaml';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidVersionCommand()
    {
        return 'pnpm --version';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidInstallCommand()
    {
        return 'pnpm install';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidUpdateCommand()
    {
        return 'pnpm update';
    }
}
