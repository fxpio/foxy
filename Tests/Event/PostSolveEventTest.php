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

use Foxy\Event\PostSolveEvent;

/**
 * Tests for post solve event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PostSolveEventTest extends AbstractSolveEventTest
{
    /**
     * {@inheritdoc}
     *
     * @return PostSolveEvent
     */
    public function getEvent()
    {
        return new PostSolveEvent($this->assetDir, $this->packages, 42);
    }

    public function testGetRunResult()
    {
        $event = $this->getEvent();
        static::assertSame(42, $event->getRunResult());
    }
}
