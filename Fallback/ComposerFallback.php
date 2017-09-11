<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Fallback;

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
class ComposerFallback implements FallbackInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

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
     * @param Composer        $composer The composer
     * @param IOInterface     $io       The IO
     * @param Config          $config   The config
     * @param Filesystem|null $fs       The composer filesystem
     */
    public function __construct(Composer $composer, IOInterface $io, Config $config, Filesystem $fs = null)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $config;
        $this->fs = $fs ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $rm = $this->composer->getRepositoryManager();
        $im = $this->composer->getInstallationManager();
        $composerFile = Factory::getComposerFile();
        $lockFile = str_replace('.json', '.lock', $composerFile);
        $locker = new Locker($this->io, new JsonFile($lockFile, null, $this->io), $rm, $im, file_get_contents($composerFile));

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
    public function restore()
    {
        if (!$this->config->get('fallback-composer')) {
            return;
        }

        $this->io->write('<info>Fallback to previous state</info>');
        $hasLock = $this->restoreLockData();

        if ($hasLock) {
            $this->restorePreviousLockFile();
        } else {
            $this->fs->remove($this->composer->getConfig()->get('vendor-dir'));
        }
    }

    /**
     * Restore the data of lock file.
     *
     * @return bool
     */
    protected function restoreLockData()
    {
        $this->composer->getLocker()->setLockData(
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

        return $this->composer->getLocker()->isLocked();
    }

    /**
     * Restore the PHP dependencies with the previous lock file.
     */
    protected function restorePreviousLockFile()
    {
        $input = ConsoleUtil::getInput($this->io);
        $config = $this->composer->getConfig();
        list($preferSource, $preferDist) = ConsoleUtil::getPreferredInstallOptions($config, $input);
        $optimize = $input->getOption('optimize-autoloader') || $config->get('optimize-autoloader');
        $authoritative = $input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
        $apcu = $input->getOption('apcu-autoloader') || $config->get('apcu-autoloader');

        Installer::create($this->io, $this->composer)
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
