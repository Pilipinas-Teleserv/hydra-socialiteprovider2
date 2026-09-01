<?php

namespace SocialiteProviders\Teleserv\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Teleserv\Events\UserLoggedIn;
use SocialiteProviders\Teleserv\UserRequirements;

class CallbackController
{
    public function __invoke(): RedirectResponse
    {
        $ermUser = Socialite::driver('teleserv')->user();

        $userClass = config('hydra.user');

        $user = $userClass::query()->firstOrNew(['email' => $ermUser->email]);

        UserRequirements::ensure($user);

        if (! $user->exists) {
            $user->id = $ermUser->id;
        }

        $user->first_name = $ermUser->first_name;
        $user->middle_name = $ermUser->middle_name;
        $user->last_name = $ermUser->last_name;
        $user->employee_code = $ermUser->employee_code;
        $user->email = $ermUser->email;
        $user->password = Str::password();
        $user->save();

        Auth::login($user, (bool) config('hydra.remember_me', false));

        event(new UserLoggedIn($user, $ermUser));

        return redirect()->intended(config('hydra.redirect_to', '/'));
    }
}
