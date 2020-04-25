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
use Composer\Util\Filesystem;
use Foxy\Config\Config;
use Foxy\Util\ConsoleUtil;
use Foxy\Util\LockerUtil;
use Foxy\Util\PackageUtil;
use Symfony\Component\Console\Input\InputInterface;

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
     * @var InputInterface
     */
    protected $input;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var null|Installer
     */
    protected $installer;

    /**
     * @var array
     */
    protected $lock = array();

    /**
     * Constructor.
     *
     * @param Composer        $composer  The composer
     * @param IOInterface     $io        The IO
     * @param Config          $config    The config
     * @param InputInterface  $input     The input
     * @param null|Filesystem $fs        The composer filesystem
     * @param null|Installer  $installer The installer
     */
    public function __construct(
        Composer $composer,
        IOInterface $io,
        Config $config,
        InputInterface $input,
        Filesystem $fs = null,
        Installer $installer = null
    ) {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $config;
        $this->input = $input;
        $this->fs = $fs ?: new Filesystem();
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $rm = $this->composer->getRepositoryManager();
        $im = $this->composer->getInstallationManager();
        $composerFile = Factory::getComposerFile();
        $locker = LockerUtil::getLocker($this->io, $rm, $im, $composerFile);

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

        $this->io->write('<info>Fallback to previous state for Composer</info>');
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

        $isLocked = $this->composer->getLocker()->isLocked();
        $lockData = $isLocked ? $this->composer->getLocker()->getLockData() : null;
        $hasPackage = \is_array($lockData) && isset($lockData['packages']) && !empty($lockData['packages']);

        return $isLocked && $hasPackage;
    }

    /**
     * Restore the PHP dependencies with the previous lock file.
     */
    protected function restorePreviousLockFile()
    {
        $config = $this->composer->getConfig();
        list($preferSource, $preferDist) = ConsoleUtil::getPreferredInstallOptions($config, $this->input);
        $optimize = $this->input->getOption('optimize-autoloader') || $config->get('optimize-autoloader');
        $authoritative = $this->input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
        $apcu = $this->input->getOption('apcu-autoloader') || $config->get('apcu-autoloader');

        $installer = $this->getInstaller()
            ->setVerbose($this->input->getOption('verbose'))
            ->setPreferSource($preferSource)
            ->setPreferDist($preferDist)
            ->setDevMode(!$this->input->getOption('no-dev'))
            ->setDumpAutoloader(!$this->input->getOption('no-autoloader'))
            ->setRunScripts(false)
            ->setOptimizeAutoloader($optimize)
            ->setClassMapAuthoritative($authoritative)
            ->setApcuAutoloader($apcu)
            ->setIgnorePlatformRequirements($this->input->getOption('ignore-platform-reqs'))
        ;

        // @codeCoverageIgnoreStart
        if (method_exists($installer, 'setSkipSuggest')) {
            $installer->setSkipSuggest(true);
        }
        // @codeCoverageIgnoreEnd

        $installer->run();
    }

    /**
     * Get the lock value.
     *
     * @param string     $key     The key
     * @param null|mixed $default The default value
     *
     * @return null|mixed
     */
    private function getLockValue($key, $default = null)
    {
        return isset($this->lock[$key]) ? $this->lock[$key] : $default;
    }

    /**
     * Get the installer.
     *
     * @return Installer
     */
    private function getInstaller()
    {
        return null !== $this->installer ? $this->installer : Installer::create($this->io, $this->composer);
    }
}
