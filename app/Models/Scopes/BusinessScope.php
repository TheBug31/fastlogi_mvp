<?php

namespace App\Models\Scopes;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BusinessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Superadmins can inspect all tenants without automatic filtering
            if ($user->role === UserRole::Superadmin) {
                return;
            }

            // If user belongs to a business (Owner or Driver), filter by their business_id
            if ($user->business_id !== null) {
                $builder->where($model->qualifyColumn('business_id'), $user->business_id);
            }
        }
    }
}
