<?php

/*
 * This file is part of the Foxy package.
 *
 * @author (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Foxy\Tests\Fixtures\Util;

use Composer\Composer;
use Composer\Util\ProcessExecutor;

/*
 * Mock of ProcessExecutor.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
if (version_compare(Composer::VERSION, '2.3.0', '<')) {
    class ProcessExecutorMock extends AbstractProcessExecutorMock
    {
        /**
         * {@inheritdoc}
         */
        public function execute($command, &$output = null, $cwd = null)
        {
            return $this->doExecute($command, $output, $cwd);
        }
    }
} else {
    class ProcessExecutorMock extends AbstractProcessExecutorMock
    {
        /**
         * {@inheritdoc}
         */
        public function execute($command, &$output = null, ?string $cwd = null): int
        {
            return $this->doExecute($command, $output, $cwd);
        }
    }
}
