<?php

/*
 * This file is part of the Foxy package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Event;

use Foxy\Event\GetAssetsEvent;

/**
 * Tests for get assets event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class GetAssetsEventTest extends AbstractSolveEventTest
{
    /**
     * @var array
     */
    protected $assets = array(
        '@composer-asset/foo--bar' => 'file:./vendor/foxy/composer-asset/foo/bar',
    );

    /**
     * {@inheritdoc}
     *
     * @return GetAssetsEvent
     */
    public function getEvent()
    {
        return new GetAssetsEvent($this->assetDir, $this->packages, $this->assets);
    }

    public function testHasAsset()
    {
        $event = $this->getEvent();
        static::assertTrue($event->hasAsset('@composer-asset/foo--bar'));
    }

    public function testAddAsset()
    {
        $assetPackageName = '@composer-asset/bar--foo';
        $assetPackagePath = 'file:./vendor/foxy/composer-asset/bar/foo';
        $event = $this->getEvent();

        static::assertFalse($event->hasAsset($assetPackageName));
        $event->addAsset($assetPackageName, $assetPackagePath);
        static::assertTrue($event->hasAsset($assetPackageName));
    }

    public function testGetAssets()
    {
        $event = $this->getEvent();
        static::assertSame($this->assets, $event->getAssets());

        $expectedAssets = array(
            '@composer-asset/foo--bar' => 'file:./vendor/foxy/composer-asset/foo/bar',
            '@composer-asset/bar--foo' => 'file:./vendor/foxy/composer-asset/bar/foo',
        );

        $event->addAsset('@composer-asset/bar--foo', 'file:./vendor/foxy/composer-asset/bar/foo');
        static::assertSame($expectedAssets, $event->getAssets());
    }
}
