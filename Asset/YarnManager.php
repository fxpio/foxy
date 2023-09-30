<?php

/**
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Asset;

use Composer\Semver\VersionParser;

/**
 * Yarn Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class YarnManager extends AbstractAssetManager
{
    // phpcs:disable PEAR.Commenting.FunctionComment.MissingReturn

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
        if ($this->isYarnNext()) {
            return true;
        }

        $cmd = $this->buildCommand('yarn', 'check', $this->mergeInteractiveCommand(array('check')));

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
        return $this->buildCommand('yarn', 'install', $this->mergeInteractiveCommand(array('install')));
    }

    /**
     * {@inheritdoc}
     */
    protected function getUpdateCommand()
    {
        $commandName = $this->isYarnNext() ? 'up' : 'upgrade';

        return $this->buildCommand('yarn', 'update', $this->mergeInteractiveCommand(array($commandName)));
    }

    // phpcs:enable PEAR.Commenting.FunctionComment.MissingReturn

    /**
     * @return bool
     */
    private function isYarnNext()
    {
        $version = $this->getVersion();
        $parser = new VersionParser();
        $constraint = $parser->parseConstraints('>=2.0.0');

        return $constraint->matches($parser->parseConstraints($version));
    }

    /**
     * Merge Interactive Command
     *
     * @param array $command the command to append to
     *
     * @return array
     */
    private function mergeInteractiveCommand(array $command)
    {
        if (!$this->isYarnNext()) {
            $command[] = '--non-interactive';
        }

        return $command;
    }
}
