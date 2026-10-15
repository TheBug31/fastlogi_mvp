<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\User;
use App\Services\BusinessService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function businessPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Tienda Nueva',
        'address' => 'Calle 10 # 20-30, Bogotá',
        'phone' => '6013334444',
        'owner_name' => 'Laura Jiménez',
        'owner_email' => 'laura@tiendanueva.test',
        'password' => 'secret123',
    ], $overrides);
}

test('createWithOwner persists an active business and its active owner with a hashed password', function () {
    $business = app(BusinessService::class)->createWithOwner(businessPayload());

    $owner = User::where('email', 'laura@tiendanueva.test')->first();

    expect($business->status->value)->toBe('active')
        ->and($owner)->not->toBeNull()
        ->and($owner->business_id)->toBe($business->id)
        ->and($owner->role)->toBe(UserRole::Owner)
        ->and($owner->status)->toBe(UserStatus::Active)
        ->and($owner->phone)->toBe('6013334444')
        ->and(Hash::check('secret123', $owner->password))->toBeTrue();
});

test('the owner created for a new business can log in', function () {
    app(BusinessService::class)->createWithOwner(businessPayload([
        'owner_email' => 'nuevo@dueno.test',
    ]));

    $this->post('/login', [
        'identifier' => 'nuevo@dueno.test',
        'password' => 'secret123',
    ])->assertRedirect(route('owner.dashboard'));

    $this->assertAuthenticated();
});

test('createWithOwner is atomic: a failing owner insert rolls the business back', function () {
    User::factory()->create(['email' => 'duplicado@fastlogi.test']);
    $before = Business::count();

    expect(fn () => app(BusinessService::class)->createWithOwner(businessPayload([
        'name' => 'Negocio Fantasma',
        'owner_email' => 'duplicado@fastlogi.test',
    ])))->toThrow(QueryException::class);

    expect(Business::count())->toBe($before)
        ->and(Business::where('name', 'Negocio Fantasma')->exists())->toBeFalse();
});
