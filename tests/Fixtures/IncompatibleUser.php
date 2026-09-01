<?php

namespace SocialiteProviders\Teleserv\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

class IncompatibleUser extends Authenticatable
{
    public $incrementing = false;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
