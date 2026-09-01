<?php

namespace SocialiteProviders\Teleserv;

use InvalidArgumentException;

class HydraBase
{
    public static function fromConfig(): string
    {
        return self::normalize(config('services.teleserv.base'));
    }

    public static function normalize(mixed $base): string
    {
        if (! is_string($base) || $base === '') {
            throw new InvalidArgumentException(
                'Set services.teleserv.base (TELESERV_BASE) to your Hydra SSO URL.'
            );
        }

        return rtrim($base, '/');
    }
}
