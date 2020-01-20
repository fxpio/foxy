<?php
namespace Foxy\Asset;

use InvalidArgumentException;

class AssetManagerResult
{
    private $exitCode;
    private $errorReason;
    private $command;

    public function __construct($command, $exitCode, $errorReason = null)
    {
        if ($exitCode > 0 && empty($errorReason))
        {
            throw new InvalidArgumentException(
                'If the result is != 0 an error reason must be supplied!',
                1591974481
            );
        }

        $this->exitCode = $exitCode;
        $this->errorReason = $errorReason;
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @return null
     */
    public function getErrorReason()
    {
        return $this->errorReason;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

}