<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Solver;

use Composer\Composer;
use Composer\Factory;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Locker;
use Composer\Util\Filesystem;
use Foxy\Config\Config;
use Foxy\Util\ConsoleUtil;
use Foxy\Util\PackageUtil;

/**
 * Composer fallback.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ComposerFallback implements ComposerFallbackInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var array
     */
    protected $lock = array();

    /**
     * Constructor.
     *
     * @param Config          $config The config
     * @param Filesystem|null $fs     The composer filesystem
     */
    public function __construct(Config $config, Filesystem $fs = null)
    {
        $this->config = $config;
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function saveLockFile(Composer $composer, IOInterface $io)
    {
        $rm = $composer->getRepositoryManager();
        $im = $composer->getInstallationManager();
        $composerFile = Factory::getComposerFile();
        $lockFile = str_replace('.json', '.lock', $composerFile);
        $locker = new Locker($io, new JsonFile($lockFile, null, $io), $rm, $im, file_get_contents($composerFile));

        try {
            $lock = $locker->getLockData();
            $this->lock = PackageUtil::loadLockPackages($lock);
        } catch (\LogicException $e) {
            $this->lock = array();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Composer $composer, IOInterface $io)
    {
        if ($this->config->get('fallback-composer')) {
            $io->write('<info>Fallback to previous state</info>');
            $hasLock = $this->restoreLockData($composer);

            if ($hasLock) {
                $this->restorePreviousLockFile($composer, $io);
            } else {
                $this->fs->remove($composer->getConfig()->get('vendor-dir'));
            }
        }

        throw new \RuntimeException('The asset manager ended with an error');
    }

    /**
     * Restore the data of lock file.
     *
     * @param Composer $composer The composer
     *
     * @return bool
     */
    protected function restoreLockData(Composer $composer)
    {
        $composer->getLocker()->setLockData(
            $this->getLockValue('packages', array()),
            $this->getLockValue('packages-dev'),
            $this->getLockValue('platform', array()),
            $this->getLockValue('platform-dev', array()),
            $this->getLockValue('aliases', array()),
            $this->getLockValue('minimum-stability'),
            $this->getLockValue('stability-flags', array()),
            $this->getLockValue('prefer-stable', false),
            $this->getLockValue('prefer-lowest', false),
            $this->getLockValue('platform-overrides', array())
        );

        return $composer->getLocker()->isLocked();
    }

    /**
     * Restore the PHP dependencies with the previous lock file.
     *
     * @param Composer    $composer The composer
     * @param IOInterface $io       The IO
     */
    protected function restorePreviousLockFile(Composer $composer, IOInterface $io)
    {
        $input = ConsoleUtil::getInput($io);
        $config = $composer->getConfig();
        list($preferSource, $preferDist) = ConsoleUtil::getPreferredInstallOptions($config, $input);
        $optimize = $input->getOption('optimize-autoloader') || $config->get('optimize-autoloader');
        $authoritative = $input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
        $apcu = $input->getOption('apcu-autoloader') || $config->get('apcu-autoloader');

        Installer::create($io, $composer)
            ->setVerbose($input->getOption('verbose'))
            ->setPreferSource($preferSource)
            ->setPreferDist($preferDist)
            ->setDevMode(!$input->getOption('no-dev'))
            ->setDumpAutoloader(!$input->getOption('no-autoloader'))
            ->setRunScripts(false)
            ->setSkipSuggest(true)
            ->setOptimizeAutoloader($optimize)
            ->setClassMapAuthoritative($authoritative)
            ->setApcuAutoloader($apcu)
            ->setIgnorePlatformRequirements($input->getOption('ignore-platform-reqs'))
            ->run()
        ;
    }

    /**
     * Get the lock value.
     *
     * @param string     $key     The key
     * @param mixed|null $default The default value
     *
     * @return mixed|null
     */
    private function getLockValue($key, $default = null)
    {
        return isset($this->lock[$key]) ? $this->lock[$key] : $default;
    }
}
