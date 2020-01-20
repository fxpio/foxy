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
    protected function getVersionCommand()
    {
        return $this->buildCommand('npm', 'version', '--version');
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstallCommand()
    {
        $additionalOptions = array();
        if (true !== $this->isDevMode) {
            $additionalOptions = array('--prod');
        }

        return $this->buildCommand('npm', 'install', 'install', $additionalOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUpdateCommand()
    {
        $additionalOptions = array();
        if (true === $this->isDevMode) {
            $additionalOptions = array('--dev');
        }

        return $this->buildCommand('npm', 'update', 'update', $additionalOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function actionWhenComposerDependenciesAreAlreadyInstalled($names)
    {
        foreach ($names as $name) {
            $this->fs->remove(self::NODE_MODULES_PATH.'/'.$name);
        }
    }
}
