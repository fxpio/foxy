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

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;

/**
 * Asset package.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AssetPackage implements AssetPackageInterface
{
    const SECTION_DEPENDENCIES = 'dependencies';
    const SECTION_DEV_DEPENDENCIES = 'devDependencies';
    const COMPOSER_PREFIX = '@composer-asset/';

    /**
     * @var JsonFile
     */
    protected $jsonFile;

    /**
     * @var array
     */
    protected $package = array();

    /**
     * Constructor.
     *
     * @param RootPackageInterface $rootPackage The composer root package
     * @param JsonFile             $jsonFile    The json file
     */
    public function __construct(RootPackageInterface $rootPackage, JsonFile $jsonFile)
    {
        $this->jsonFile = $jsonFile;

        if ($jsonFile->exists()) {
            $this->setPackage((array) $jsonFile->read());
        }

        $this->injectRequiredKeys($rootPackage);
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        $this->jsonFile->write($this->package);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPackage(array $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstalledDependencies()
    {
        $installedAssets = array();

        if (isset($this->package[self::SECTION_DEPENDENCIES]) && \is_array($this->package[self::SECTION_DEPENDENCIES])) {
            foreach ($this->package[self::SECTION_DEPENDENCIES] as $dependency => $version) {
                if (0 === strpos($dependency, self::COMPOSER_PREFIX)) {
                    $installedAssets[$dependency] = $version;
                }
            }
        }

        return $installedAssets;
    }

    /**
     * {@inheritdoc}
     */
    public function addNewDependencies(array $dependencies)
    {
        $installedAssets = $this->getInstalledDependencies();
        $existingPackages = array();

        foreach ($dependencies as $name => $path) {
            if (isset($installedAssets[$name])) {
                $existingPackages[] = $name;
            } else {
                $this->package[self::SECTION_DEPENDENCIES][$name] = 'file:./'.\dirname($path);
            }
        }

        $this->orderPackages(self::SECTION_DEPENDENCIES);
        $this->orderPackages(self::SECTION_DEV_DEPENDENCIES);

        return $existingPackages;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUnusedDependencies(array $dependencies)
    {
        $installedAssets = $this->getInstalledDependencies();
        $removeDependencies = array_diff_key($installedAssets, $dependencies);

        foreach ($removeDependencies as $dependency => $version) {
            unset($this->package[self::SECTION_DEPENDENCIES][$dependency]);
        }

        return $this;
    }

    /**
     * Inject the required keys for asset package defined in root composer package.
     *
     * @param RootPackageInterface $rootPackage The composer root package
     */
    protected function injectRequiredKeys(RootPackageInterface $rootPackage)
    {
        if (!isset($this->package['license']) && \count($rootPackage->getLicense()) > 0) {
            $license = current($rootPackage->getLicense());

            if ('proprietary' === $license) {
                if (!isset($this->package['private'])) {
                    $this->package['private'] = true;
                }
            } else {
                $this->package['license'] = $license;
            }
        }
    }

    /**
     * Order the packages section.
     *
     * @param string $section The package section
     */
    protected function orderPackages($section)
    {
        if (isset($this->package[$section]) && \is_array($this->package[$section])) {
            ksort($this->package[$section], SORT_STRING);
        }
    }
}
