<?php

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Collaborator = 'collaborator';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $role) => $role->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Dirigeant',
            self::Collaborator => 'Collaborateur',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $role) => ['value' => $role->value, 'label' => $role->label()],
            self::cases(),
        );
    }
}

