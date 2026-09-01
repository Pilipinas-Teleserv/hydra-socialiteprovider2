<?php

namespace SocialiteProviders\Teleserv\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserLoggedIn
{
    use Dispatchable;

    public function __construct(
        public object $user,
        public object $ermUser,
    ) {}
}
