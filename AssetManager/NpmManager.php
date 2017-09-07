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
 * NPM Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NpmManager extends AbstractAssetManager
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'npm';
    }

    /**
     * {@inheritdoc}
     */
    public function getLockPackageName()
    {
        return 'package-lock.json';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionCommand()
    {
        return 'npm --version';
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallCommand()
    {
        return $this->buildCommand('npm', 'install', 'install');
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateCommand()
    {
        return $this->buildCommand('npm', 'update', 'update');
    }

    /**
     * {@inheritdoc}
     */
    protected function actionWhenComposerDependencyIsAlreadyInstalled($name)
    {
        $this->fs->remove('./node_modules/'.$name);
    }
}
