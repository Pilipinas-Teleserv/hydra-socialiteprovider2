<?php

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SocialiteProviders\Teleserv\Exceptions\IncompatibleUserModelException;
use SocialiteProviders\Teleserv\Tests\Fixtures\IncompatibleUser;
use SocialiteProviders\Teleserv\Tests\Fixtures\User;
use SocialiteProviders\Teleserv\UserRequirements;

it('accepts a user model that guards nothing', function () {
    expect(UserRequirements::ensure(new User))->toBeInstanceOf(User::class);
});

it('throws when the user model uses laravel default fillable attributes', function () {
    expect(fn () => UserRequirements::ensure(new IncompatibleUser))
        ->toThrow(IncompatibleUserModelException::class, 'first_name, middle_name, last_name, employee_code');
});

it('accepts a user model that lists hydra fields as fillable', function () {
    $user = new class extends Authenticatable
    {
        protected $fillable = [
            'first_name',
            'middle_name',
            'last_name',
            'employee_code',
            'email',
            'password',
        ];
    };

    expect(UserRequirements::ensure($user))->toBe($user);
});

it('accepts a user model with declared hydra properties', function () {
    $user = new class extends Authenticatable
    {
        protected $guarded = ['*'];

        public ?string $first_name = null;

        public ?string $middle_name = null;

        public ?string $last_name = null;

        public ?string $employee_code = null;
    };

    expect(UserRequirements::ensure($user))->toBe($user);
});

it('accepts a user model with hydra set mutators', function () {
    $user = new class extends Authenticatable
    {
        protected $guarded = ['*'];

        public function setFirstNameAttribute(?string $value): void
        {
            $this->attributes['first_name'] = $value;
        }

        public function setMiddleNameAttribute(?string $value): void
        {
            $this->attributes['middle_name'] = $value;
        }

        public function setLastNameAttribute(?string $value): void
        {
            $this->attributes['last_name'] = $value;
        }

        public function setEmployeeCodeAttribute(?string $value): void
        {
            $this->attributes['employee_code'] = $value;
        }
    };

    expect(UserRequirements::ensure($user))->toBe($user);
});

it('accepts a user model with hydra attribute setters', function () {
    $user = new class extends Authenticatable
    {
        protected $guarded = ['*'];

        protected function firstName(): Attribute
        {
            return Attribute::set(fn (?string $value) => ['first_name' => $value]);
        }

        protected function middleName(): Attribute
        {
            return Attribute::set(fn (?string $value) => ['middle_name' => $value]);
        }

        protected function lastName(): Attribute
        {
            return Attribute::set(fn (?string $value) => ['last_name' => $value]);
        }

        protected function employeeCode(): Attribute
        {
            return Attribute::set(fn (?string $value) => ['employee_code' => $value]);
        }
    };

    expect(UserRequirements::ensure($user))->toBe($user);
});
