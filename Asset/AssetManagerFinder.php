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

use Foxy\Exception\RuntimeException;

/**
 * Asset Manager finder.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetManagerFinder
{
    /**
     * @var AssetManagerInterface[]
     */
    private $managers;

    /**
     * Constructor.
     *
     * @param AssetManagerInterface[] $managers The asset managers
     */
    public function __construct(array $managers = array())
    {
        foreach ($managers as $manager) {
            if ($manager instanceof AssetManagerInterface) {
                $this->addManager($manager);
            }
        }
    }

    public function addManager(AssetManagerInterface $manager)
    {
        $this->managers[$manager->getName()] = $manager;
    }

    /**
     * Find the asset manager.
     *
     * @param null|string $manager The name of the asset manager
     *
     * @throws RuntimeException When the asset manager does not exist
     * @throws RuntimeException When the asset manager is not found
     *
     * @return AssetManagerInterface
     */
    public function findManager($manager = null)
    {
        if (null !== $manager) {
            if (isset($this->managers[$manager])) {
                return $this->managers[$manager];
            }

            throw new RuntimeException(sprintf('The asset manager "%s" doesn\'t exist', $manager));
        }

        return $this->findAvailableManager();
    }

    /**
     * Find the available asset manager.
     *
     * @throws RuntimeException When no asset manager is found
     *
     * @return AssetManagerInterface
     */
    private function findAvailableManager()
    {
        // find asset manager by lockfile
        foreach ($this->managers as $manager) {
            if ($manager->hasLockFile()) {
                return $manager;
            }
        }

        // find asset manager by availability
        foreach ($this->managers as $manager) {
            if ($manager->isAvailable()) {
                return $manager;
            }
        }

        throw new RuntimeException('No asset manager is found');
    }
}
