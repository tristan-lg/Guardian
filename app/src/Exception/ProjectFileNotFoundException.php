<?php

namespace App\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ProjectFileNotFoundException extends Exception
{
    #[Pure] public function __construct(string $fileKey = "", ?Throwable $previous = null)
    {
        parent::__construct("The file with key '{$fileKey}' was not found on project repository. Maybe you need to scan the project.", 400, $previous);
    }
}
