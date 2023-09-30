<?php

/*
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Asset;

/**
 * Pnpm Manager.
 *
 * @author Steffen Dietz <steffo.dietz@gmail.com>
 */
class PnpmManager extends AbstractAssetManager
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pnpm';
    }

    /**
     * {@inheritdoc}
     */
    public function getLockPackageName()
    {
        return 'pnpm-lock.yaml';
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return parent::isInstalled() && file_exists($this->getLockPackageName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getVersionCommand()
    {
        return $this->buildCommand('pnpm', 'version', '--version');
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstallCommand()
    {
        return $this->buildCommand('pnpm', 'install', 'install');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUpdateCommand()
    {
        return $this->buildCommand('pnpm', 'update', 'update');
    }
}
