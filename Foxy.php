<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Foxy\Asset\AssetManagerFinder;
use Foxy\Asset\AssetManagerInterface;
use Foxy\Config\Config;
use Foxy\Config\ConfigBuilder;
use Foxy\Exception\RuntimeException;
use Foxy\Fallback\AssetFallback;
use Foxy\Fallback\ComposerFallback;
use Foxy\Solver\Solver;
use Foxy\Solver\SolverInterface;
use Foxy\Util\ComposerUtil;
use Foxy\Util\ConsoleUtil;

/**
 * Composer plugin.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Foxy implements PluginInterface, EventSubscriberInterface
{
    const REQUIRED_COMPOSER_VERSION = '^1.5.0|^2.0.0';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AssetManagerInterface
     */
    protected $assetManager;

    /**
     * @var AssetFallback
     */
    protected $assetFallback;

    /**
     * @var ComposerFallback
     */
    protected $composerFallback;

    /**
     * @var SolverInterface
     */
    protected $solver;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * The list of the classes of asset managers.
     */
    private static $assetManagers = array(
        'Foxy\Asset\YarnManager',
        'Foxy\Asset\NpmManager',
    );

    /**
     * The default values of config.
     */
    private static $defaultConfig = array(
        'enabled' => true,
        'manager' => null,
        'manager-version' => array(
            'npm' => '>=5.0.0',
            'yarn' => '>=1.0.0',
        ),
        'manager-bin' => null,
        'manager-options' => null,
        'manager-install-options' => null,
        'manager-update-options' => null,
        'manager-timeout' => null,
        'composer-asset-dir' => null,
        'run-asset-manager' => true,
        'fallback-asset' => true,
        'fallback-composer' => true,
        'enable-packages' => array(),
    );

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ComposerUtil::getInitEventName() => array(
                array('init', 100),
            ),
            PackageEvents::POST_PACKAGE_INSTALL => array(
                array('initOnInstall', 100),
            ),
            ScriptEvents::POST_INSTALL_CMD => array(
                array('solveAssets', 100),
            ),
            ScriptEvents::POST_UPDATE_CMD => array(
                array('solveAssets', 100),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        ComposerUtil::validateVersion(static::REQUIRED_COMPOSER_VERSION, Composer::VERSION);

        $input = ConsoleUtil::getInput($io);
        $executor = new ProcessExecutor($io);
        $fs = new Filesystem($executor);

        $this->config = ConfigBuilder::build($composer, self::$defaultConfig, $io);
        $this->assetManager = $this->getAssetManager($io, $this->config, $executor, $fs);
        $this->assetFallback = new AssetFallback($io, $this->config, $this->assetManager->getPackageName(), $fs);
        $this->composerFallback = new ComposerFallback($composer, $io, $this->config, $input, $fs);
        $this->solver = new Solver($this->assetManager, $this->config, $fs, $this->composerFallback);

        $this->assetManager->setFallback($this->assetFallback);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Do nothing
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Do nothing
    }

    /**
     * Init the plugin just after the first installation.
     *
     * @param PackageEvent $event The package event
     */
    public function initOnInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();

        if ($operation instanceof InstallOperation && 'foxy/foxy' === $operation->getPackage()->getName()) {
            $this->init();
        }
    }

    /**
     * Init the plugin.
     */
    public function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $this->assetFallback->save();
            $this->composerFallback->save();

            if ($this->config->get('enabled')) {
                $this->assetManager->validate();
            }
        }
    }

    /**
     * Set the solver.
     *
     * @param SolverInterface $solver The solver
     */
    public function setSolver(SolverInterface $solver)
    {
        $this->solver = $solver;
    }

    /**
     * Solve the assets.
     *
     * @param Event $event The composer script event
     */
    public function solveAssets(Event $event)
    {
        $this->solver->setUpdatable(false !== strpos($event->getName(), 'update'));
        $this->solver->solve($event->getComposer(), $event->getIO());
    }

    /**
     * Get the asset manager.
     *
     * @param IOInterface     $io       The IO
     * @param Config          $config   The config
     * @param ProcessExecutor $executor The process executor
     * @param Filesystem      $fs       The composer filesystem
     *
     * @throws RuntimeException When the asset manager is not found
     *
     * @return AssetManagerInterface
     */
    protected function getAssetManager(IOInterface $io, Config $config, ProcessExecutor $executor, Filesystem $fs)
    {
        $amf = new AssetManagerFinder();

        foreach (self::$assetManagers as $class) {
            $amf->addManager(new $class($io, $config, $executor, $fs));
        }

        return $amf->findManager($config->get('manager'));
    }
}
