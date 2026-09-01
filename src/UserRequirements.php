<?php

namespace SocialiteProviders\Teleserv;

use SocialiteProviders\Teleserv\Exceptions\IncompatibleUserModelException;
use Throwable;

class UserRequirements
{
    /**
     * @var list<string>
     */
    public const REQUIRED_COLUMNS = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_code',
    ];

    public static function ensure(?object $model = null): object
    {
        $model ??= new (config('hydra.user'));

        try {
            $schema = $model->getConnection()->getSchemaBuilder();
            $table = $model->getTable();
        } catch (Throwable $exception) {
            throw IncompatibleUserModelException::unreadable($model::class, $exception);
        }

        $missing = [];

        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! $schema->hasColumn($table, $column)) {
                $missing[] = $column;
            }
        }

        if ($missing !== []) {
            throw IncompatibleUserModelException::missingColumns($model::class, $missing);
        }

        return $model;
    }
}
