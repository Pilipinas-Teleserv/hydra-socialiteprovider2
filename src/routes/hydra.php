<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Teleserv\Http\Controllers\CallbackController;
use SocialiteProviders\Teleserv\HydraBase;

Route::middleware('web')->group(function (): void {
    Route::get('auth/login', function () {
        return Socialite::driver('teleserv')->redirect();
    })->name('login')->middleware('guest');

    Route::post('auth/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(HydraBase::fromConfig().'/auth/logout');
    })->name('logout')->middleware('auth');

    Route::get('auth/change-password', function () {
        return redirect(HydraBase::fromConfig().'/change-password');
    })->name('change-password')->middleware('auth');

    Route::get('auth/teleserv/callback', CallbackController::class);
});
