<?php

namespace SocialiteProviders\Teleserv\Exceptions;

use RuntimeException;

class IncompatibleUserModelException extends RuntimeException
{
    /**
     * @param  list<string>  $missing
     */
    public static function missingAttributes(string $model, array $missing): self
    {
        $attributes = implode(', ', $missing);

        return new self(
            "The [{$model}] user model is not ready for Hydra. Missing required attributes: {$attributes}. The User model must accept first_name, middle_name, last_name, and employee_code."
        );
    }
}
