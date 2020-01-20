<?php

namespace Foxy\Tests\FunctionalTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class DependenciesFunctionalTest extends TestCase
{
    private $filesystem;
    /** @var string */
    private $temporaryProjectDirectory;

    protected function setUp()
    {
        $this->filesystem = new Filesystem();

        $this->cleanUpFiles();
    }

    protected function tearDown()
    {
        $this->cleanUpFiles();
    }

    /**
     * Test foxy setting Dev & Prod dependencies.
     *
     * @large
     */
    public function testInstallationOfDependencies()
    {
        $testRootDirectory = '/tmp/foxyroot';
        $copyCommand = 'mkdir '.$testRootDirectory.' && cp -R '.__DIR__.'/../../ '.$testRootDirectory;

        (Process::fromShellCommandline($copyCommand))->mustRun();

        $processComposerInstall = Process::fromShellCommandline('composer --working-dir='.__DIR__.'/foxyMain install');
        $processComposerInstall->run();

        static::assertSame(
            0,
            $processComposerInstall->getExitCode(),
            'Composer could not install test project foxyMain.'.PHP_EOL.
            'Command output:'.PHP_EOL.
            $processComposerInstall->getOutput()
        );

        $this->assertFileExistVerbose('/foxyMain/package.json', $processComposerInstall->getOutput());
        $packageJson = file_get_contents(__DIR__.'/foxyMain/package.json');

        $this->assertFileExistVerbose('/foxyMain/package-lock.json', $processComposerInstall->getOutput());
        $packageLockJson = file_get_contents(__DIR__.'/foxyMain/package-lock.json');

        $processComposerInstallNoDev = Process::fromShellCommandline('composer --working-dir='.__DIR__.'/foxyMain install --no-dev');
        $processComposerInstallNoDev->run();

        static::assertSame(
            0,
            $processComposerInstallNoDev->getExitCode(),
            'Composer could not install test project foxyMain with --no-dev.'.PHP_EOL.
            'Command output:'.PHP_EOL.
            $processComposerInstallNoDev->getOutput()
        );

        $this->assertFileExistVerbose('/foxyMain/package.json', $processComposerInstallNoDev->getOutput());
        $packageJsonNoDev = file_get_contents(__DIR__.'/foxyMain/package.json');

        $this->assertFileExistVerbose('/foxyMain/package-lock.json', $processComposerInstallNoDev->getOutput());
        $packageLockJsonNoDev = file_get_contents(__DIR__.'/foxyMain/package-lock.json');

        static::assertSame($packageJson, $packageJsonNoDev);
        static::assertSame($packageLockJson, $packageLockJsonNoDev);
    }

    /**
     * Deleting files used while testing.
     */
    private function cleanUpFiles()
    {
        $this->filesystem->remove(__DIR__.'/foxyMain/composer.lock');
        $this->filesystem->remove(__DIR__.'/foxyMain/node_modules');
        $this->filesystem->remove(__DIR__.'/foxyMain/package.json');
        $this->filesystem->remove(__DIR__.'/foxyMain/yarn.lock');
        $this->filesystem->remove(__DIR__.'/foxyMain/package-lock.json');
        $this->filesystem->remove(__DIR__.'/foxyMain/vendor');
        $this->filesystem->remove('/tmp/foxyroot');
    }

    /**
     * Checks if file exists. Output error and debug information if it doesn't.
     *
     * @param string $file
     * @param string $commandOutput
     */
    private function assertFileExistVerbose($file, $commandOutput)
    {
        $directory = dirname($file);
        static::assertFileExists(
            __DIR__.$file,
            $file.' did not exist. '.PHP_EOL.
            'Contant of foxyMain'.PHP_EOL.
            implode(PHP_EOL, scandir(__DIR__ . $directory)).PHP_EOL.
            'Command which should have resulted in file:'.PHP_EOL.
            $commandOutput
        );
    }
}
