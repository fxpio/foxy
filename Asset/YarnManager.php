<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Asset;

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
    public function isInstalled()
    {
        return parent::isInstalled() && file_exists($this->getLockPackageName());
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForUpdate()
    {
        $cmd = $this->buildCommand('yarn', 'check', 'check --non-interactive');

        return 0 === $this->executor->execute($cmd);
    }

    /**
     * {@inheritdoc}
     */
    protected function getVersionCommand()
    {
        return $this->buildCommand('yarn', 'version', '--version');
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstallCommand()
    {
        return $this->buildCommand('yarn', 'install', 'install --non-interactive');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUpdateCommand()
    {
        return $this->buildCommand('yarn', 'update', 'upgrade --non-interactive');
    }
}
