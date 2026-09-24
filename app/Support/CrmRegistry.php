<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;

class CrmRegistry
{
    public static function morphMap(): array
    {
        return [
            'lead' => Lead::class,
            'account' => Account::class,
            'contact' => Contact::class,
            'opportunity' => Opportunity::class,
            'case' => CrmCase::class,
            'task' => Task::class,
            'event' => Event::class,
        ];
    }

    public static function modelFor(string $key): ?string
    {
        return self::morphMap()[$key] ?? null;
    }

    public static function keyFor(string $class): ?string
    {
        return array_search($class, self::morphMap(), true) ?: null;
    }
}
