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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
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
    const REQUIRED_COMPOSER_VERSION = '1.5.0';

    /**
     * The list of the classes of asset managers.
     */
    const ASSET_MANAGERS = array(
        'Foxy\Asset\NpmManager',
        'Foxy\Asset\YarnManager',
    );

    /**
     * The default values of config.
     */
    const DEFAULT_CONFIG = array(
        'enabled' => true,
        'manager' => 'npm',
        'manager-version' => null,
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
     * @var SolverInterface
     */
    protected $solver;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
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
        $config = ConfigBuilder::build($composer, self::DEFAULT_CONFIG, $io);
        $executor = new ProcessExecutor($io);
        $fs = new Filesystem($executor);
        $assetManager = $this->getAssetManager($io, $config, $executor, $fs);
        $assetFallback = new AssetFallback($io, $config, $assetManager->getPackageName(), $fs);
        $composerFallback = new ComposerFallback($composer, $io, $config, $input, $fs);
        $this->solver = new Solver($assetManager, $config, $fs, $composerFallback);

        $assetFallback->save();
        $composerFallback->save();
        $assetManager->setFallback($assetFallback);

        if ($config->get('enabled')) {
            $assetManager->validate();
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
     * @return AssetManagerInterface
     *
     * @throws RuntimeException When the asset manager is not found
     */
    protected function getAssetManager(IOInterface $io, Config $config, ProcessExecutor $executor, Filesystem $fs)
    {
        $manager = $config->get('manager');

        foreach (static::ASSET_MANAGERS as $class) {
            $am = new $class($io, $config, $executor, $fs);

            if ($am instanceof AssetManagerInterface && $manager === $am->getName()) {
                return $am;
            }
        }

        throw new RuntimeException(sprintf('The asset manager "%s" doesn\'t exist', $manager));
    }
}
