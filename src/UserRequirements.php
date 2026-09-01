<?php

namespace SocialiteProviders\Teleserv;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use SocialiteProviders\Teleserv\Exceptions\IncompatibleUserModelException;

class UserRequirements
{
    /**
     * @var list<string>
     */
    public const REQUIRED_ATTRIBUTES = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_code',
    ];

    public static function ensure(object $model): object
    {
        $missing = [];

        foreach (self::REQUIRED_ATTRIBUTES as $attribute) {
            if (! self::accepts($model, $attribute)) {
                $missing[] = $attribute;
            }
        }

        if ($missing !== []) {
            throw IncompatibleUserModelException::missingAttributes($model::class, $missing);
        }

        return $model;
    }

    private static function accepts(object $model, string $attribute): bool
    {
        if (! $model instanceof Model) {
            return false;
        }

        if ($model->isFillable($attribute)) {
            return true;
        }

        if ($model->hasSetMutator($attribute)) {
            return true;
        }

        if ($model->hasAttributeMutator($attribute)) {
            return true;
        }

        return self::hasDeclaredAttribute($model, $attribute);
    }

    private static function hasDeclaredAttribute(Model $model, string $attribute): bool
    {
        $reflection = new ReflectionClass($model);

        if (! $reflection->hasProperty($attribute)) {
            return false;
        }

        return $reflection->getProperty($attribute)->getDeclaringClass()->getName() !== Model::class;
    }
}
