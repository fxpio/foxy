<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\AssetManager;

/**
 * Yarn Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class YarnManager extends AbstractAssetManager
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yarn';
    }

    /**
     * {@inheritdoc}
     */
    public function getLockPackageName()
    {
        return 'yarn.lock';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionCommand()
    {
        return 'yarn --version';
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallCommand()
    {
        return $this->buildCommand('yarn', 'install', 'install --non-interactive');
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateCommand()
    {
        return $this->buildCommand('yarn', 'update', 'upgrade --non-interactive');
    }
}
