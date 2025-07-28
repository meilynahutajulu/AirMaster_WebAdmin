<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Validator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('alpha_spaces', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-zA-Z])[a-zA-Z0-9\s]+$/', $value);
        });

        Validator::replacer('alpha_spaces', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' must contain at least one word (letters) and can only contain letters, numbers, and spaces.';
        });
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
