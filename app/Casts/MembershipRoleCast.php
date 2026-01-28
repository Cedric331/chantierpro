<?php

namespace App\Casts;

use App\Enums\MembershipRole;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MembershipRoleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?MembershipRole
    {
        if ($value === null) {
            return null;
        }

        $normalized = match ($value) {
            'Customer' => MembershipRole::Collaborator->value,
            'Admin' => MembershipRole::Owner->value,
            default => $value,
        };

        return MembershipRole::tryFrom($normalized);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof MembershipRole) {
            return $value->value;
        }

        if (is_string($value)) {
            $normalized = match ($value) {
                'Customer' => MembershipRole::Collaborator->value,
                'Admin' => MembershipRole::Owner->value,
                default => $value,
            };

            return MembershipRole::tryFrom($normalized)?->value ?? $normalized;
        }

        return null;
    }
}

