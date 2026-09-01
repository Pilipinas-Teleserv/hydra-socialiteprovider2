<?php

use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Teleserv\Exceptions\IncompatibleUserModelException;
use SocialiteProviders\Teleserv\HydraBase;
use SocialiteProviders\Teleserv\Provider;

it('requires a hydra base url', function () {
    expect(fn () => HydraBase::normalize(null))
        ->toThrow(InvalidArgumentException::class, 'TELESERV_BASE');

    expect(fn () => HydraBase::normalize(''))
        ->toThrow(InvalidArgumentException::class, 'TELESERV_BASE');
});

it('trims a trailing slash from the hydra base url', function () {
    expect(HydraBase::normalize('https://hydra.example.test/'))
        ->toBe('https://hydra.example.test');
});

it('reads the hydra base url from services config', function () {
    expect(HydraBase::fromConfig())->toBe('https://hydra.example.test');
});

it('exposes the base config key to socialite manager', function () {
    expect(Provider::additionalConfigKeys())->toBe(['base']);
});

it('builds token and authorize urls from the configured hydra base', function () {
    $provider = Socialite::driver('teleserv');

    $tokenUrl = new ReflectionMethod($provider, 'getTokenUrl');
    $authUrl = new ReflectionMethod($provider, 'getAuthUrl');

    expect($tokenUrl->invoke($provider))->toBe('https://hydra.example.test/oauth/access_token');
    expect($authUrl->invoke($provider, 'state'))->toStartWith('https://hydra.example.test/oauth/authorize');
});

it('throws when the socialite provider is missing a hydra base url', function () {
    config(['services.teleserv.base' => null]);

    $provider = Socialite::driver('teleserv');
    $tokenUrl = new ReflectionMethod($provider, 'getTokenUrl');

    expect(fn () => $tokenUrl->invoke($provider))
        ->toThrow(InvalidArgumentException::class, 'TELESERV_BASE');
});

/**
 * @param  array<string, mixed>  $hydraUser
 */
function mapHydraUser(array $hydraUser): object
{
    $provider = Socialite::driver('teleserv');
    $method = new ReflectionMethod($provider, 'mapUserToObject');

    return $method->invoke($provider, $hydraUser);
}

function hydraProfile(): array
{
    return [
        'id' => 42,
        'first_name' => 'Jane',
        'middle_name' => 'Q',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
        'employee_code' => 'E42',
        'roles' => [
            ['id' => 1, 'name' => 'admin'],
        ],
    ];
}

it('maps hydra profile fields and discards avatar and roles', function () {
    $socialiteUser = mapHydraUser(hydraProfile());

    expect($socialiteUser->first_name)->toBe('Jane')
        ->and($socialiteUser->middle_name)->toBe('Q')
        ->and($socialiteUser->last_name)->toBe('Doe')
        ->and($socialiteUser->email)->toBe('jane@example.test')
        ->and($socialiteUser->employee_code)->toBe('E42')
        ->and($socialiteUser->avatar)->toBeNull()
        ->and($socialiteUser->roles)->toBeNull();
});

it('throws when the user table is missing hydra columns', function () {
    $this->createUsersTable(['name']);

    expect(fn () => mapHydraUser(hydraProfile()))
        ->toThrow(IncompatibleUserModelException::class, 'employee_code');
});
