<?php

namespace App\Services;

use App\Enums\BusinessStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BusinessService
{
    /**
     * Create a business together with its owner in a single transaction (RF-004).
     *
     * The business is born active (no approval flow) and, if anything fails,
     * neither the business nor the owner is persisted (no orphan users).
     *
     * @param  array{name: string, address: string, phone: string, owner_name: string, owner_email: string, password: string, owner_phone?: ?string}  $data
     */
    public function createWithOwner(array $data): Business
    {
        return DB::transaction(function () use ($data) {
            $business = Business::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'status' => BusinessStatus::Active,
                'suspended_at' => null,
                'inactivity_threshold_minutes' => 15,
            ]);

            User::create([
                'business_id' => $business->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => $data['owner_phone'] ?? $data['phone'],
                'password' => $data['password'],
                'role' => UserRole::Owner,
                'status' => UserStatus::Active,
            ]);

            return $business;
        });
    }
}
