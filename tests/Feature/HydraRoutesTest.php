<?php

use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use SocialiteProviders\Teleserv\Events\UserLoggedIn;
use SocialiteProviders\Teleserv\Tests\Fixtures\User;

it('registers the named hydra routes', function () {
    expect(route('login', absolute: false))->toBe('/auth/login');
    expect(route('logout', absolute: false))->toBe('/auth/logout');
    expect(route('change-password', absolute: false))->toBe('/auth/change-password');
});

it('redirects guests to the configured hydra authorize url', function () {
    $this->get('/auth/login')
        ->assertRedirect()
        ->assertRedirectContains('https://hydra.example.test/oauth/authorize');
});

it('rejects get requests to logout', function () {
    $this->get('/auth/logout')->assertMethodNotAllowed();
});

it('logs out authenticated users and redirects to hydra', function () {
    $user = User::query()->create([
        'id' => 1,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
        'password' => 'secret-password',
    ]);

    $this->actingAs($user)
        ->post('/auth/logout')
        ->assertRedirect('https://hydra.example.test/auth/logout');

    $this->assertGuest();
});

it('redirects authenticated users to hydra to change their password', function () {
    $user = User::query()->create([
        'id' => 1,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
        'password' => 'secret-password',
    ]);

    $this->actingAs($user)
        ->get('/auth/change-password')
        ->assertRedirect('https://hydra.example.test/change-password');
});

it('creates and authenticates a user from the hydra callback', function () {
    Event::fake([UserLoggedIn::class]);

    Socialite::fake('teleserv', SocialiteUser::fake([
        'id' => 42,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
    ]));

    $this->get('/auth/teleserv/callback')
        ->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'id' => 42,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
    ]);

    Event::assertDispatched(UserLoggedIn::class, function (UserLoggedIn $event): bool {
        return $event->user->email === 'jane@example.test'
            && $event->ermUser->email === 'jane@example.test';
    });
});

it('redirects to the intended url after login', function () {
    Socialite::fake('teleserv', SocialiteUser::fake([
        'id' => 42,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
    ]));

    $this->withSession(['url.intended' => '/reports'])
        ->get('/auth/teleserv/callback')
        ->assertRedirect('/reports');
});

it('falls back to a configured redirect_to when no intended url is stored', function () {
    config(['hydra.redirect_to' => '/home']);

    Socialite::fake('teleserv', SocialiteUser::fake([
        'id' => 42,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.test',
    ]));

    $this->get('/auth/teleserv/callback')
        ->assertRedirect('/home');
});
