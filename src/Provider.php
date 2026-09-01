<?php

namespace SocialiteProviders\Teleserv;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'TELESERV';

    protected $scopes = ['basic'];

    protected function getHydraBase(): string
    {
        return HydraBase::normalize($this->getConfig('base'));
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getHydraBase().'/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getHydraBase().'/oauth/access_token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getHydraBase().'/api/1.0/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        UserRequirements::ensure();

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'first_name' => $user['first_name'] ?? null,
            'middle_name' => $user['middle_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'email' => $user['email'],
            'employee_code' => $user['employee_code'] ?? null,
        ]);
    }

    public static function additionalConfigKeys(): array
    {
        return ['base'];
    }
}
