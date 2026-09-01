<?php

namespace SocialiteProviders\Teleserv\Exceptions;

use RuntimeException;
use Throwable;

class IncompatibleUserModelException extends RuntimeException
{
    /**
     * @param  list<string>  $missing
     */
    public static function missingColumns(string $model, array $missing): self
    {
        $columns = implode(', ', $missing);

        return new self(
            "The [{$model}] user model is not ready for Hydra. Missing required columns: {$columns}. The users table must include first_name, middle_name, last_name, and employee_code."
        );
    }

    public static function unreadable(string $model, Throwable $previous): self
    {
        return new self(
            "The [{$model}] user model is not ready for Hydra. The users table could not be inspected.",
            previous: $previous,
        );
    }
}
