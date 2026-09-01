# Teleserv OAuth2 Provider for Laravel Socialite

Authenticate Laravel 13 applications against a Hydra OAuth2 SSO server using Laravel Socialite.

## Requirements

- PHP 8.3+
- Laravel 13
- A Hydra SSO instance and OAuth2 client credentials
- A compatible local `User` model (see [User model compatibility](#user-model-compatibility))

This package will not work with Laravel's default `users` table (`name` only). Prepare your User model **before** you wire up login.

## User model compatibility

Integrating this package requires your application's User model and `users` table to accept Hydra profile data. On login the package writes these columns; if any are missing it throws `SocialiteProviders\Teleserv\Exceptions\IncompatibleUserModelException` and authentication does not complete.

Required columns:

| Column | Purpose |
| --- | --- |
| `first_name` | Hydra given name |
| `middle_name` | Hydra middle name (nullable is fine) |
| `last_name` | Hydra family name |
| `employee_code` | Hydra employee identifier |

Also required for local auth (Laravel defaults):

- `email` — used to find or create the user
- `password` — set to a random value on each Hydra login; the model **must** use the `hashed` password cast
- `remember_token` — used when `remember_me` is enabled

The default Laravel `name` column is not used. Do not rely on Hydra `avatar` or `roles` attributes; this package discards them.

Example migration additions:

```php
$table->string('first_name');
$table->string('middle_name')->nullable();
$table->string('last_name');
$table->string('employee_code');
```

Point `config('hydra.user')` at your User class if it is not `App\Models\User`.

## Installation

```bash
composer require hydra/socialiteprovider
```

The service provider is auto-discovered. You do not need to register providers, facades, or event listeners.

## Configuration

Add a `teleserv` entry to `config/services.php`. `base` is required and must be the origin of your Hydra SSO server.

```php
'teleserv' => [
    'client_id' => env('TELESERV_APP_ID'),
    'client_secret' => env('TELESERV_APP_SECRET'),
    'redirect' => env('TELESERV_REDIRECT', config('app.url').'/auth/teleserv/callback'),
    'base' => env('TELESERV_BASE'),
],
```

Set those values in `.env`:

```dotenv
TELESERV_APP_ID=your-client-id
TELESERV_APP_SECRET=your-client-secret
TELESERV_REDIRECT="${APP_URL}/auth/teleserv/callback"
TELESERV_BASE=https://sso.example.com
```

Optionally publish the package config:

```bash
php artisan vendor:publish --tag=hydra-config
```

```php
return [
    'user' => App\Models\User::class,
    'remember_me' => false,
    'redirect_to' => '/',
];
```

The `user` value must be a model that satisfies [User model compatibility](#user-model-compatibility).

After a successful login, the package redirects to the URL the guest originally intended (Laravel's `url.intended` session value). If none is stored, it falls back to `redirect_to`, which defaults to `/`. Override that fallback in the published config, for example `'redirect_to' => '/home'`.

## Routes

The package registers these named routes (under the `web` middleware group):

| Method | URI | Name |
| --- | --- | --- |
| GET | `/auth/login` | `login` |
| POST | `/auth/logout` | `logout` |
| GET | `/auth/change-password` | `change-password` |
| GET | `/auth/teleserv/callback` | — |

Login redirects guests to Hydra. Logout ends the local session and redirects to Hydra's logout URL. Change-password redirects authenticated users to Hydra's password form.

There should be no local registration or password-reset routes; those belong on Hydra.

## Events

After a successful login the package dispatches `SocialiteProviders\Teleserv\Events\UserLoggedIn` with:

- `$event->user` — the persisted application user
- `$event->ermUser` — the Socialite user from Hydra

Listen for it to sync any additional application state.
