<?php

namespace Konnco\FilamentTimeMatrix\Enums;

use Carbon\Carbon;

enum Day: int
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    case SUNDAY = 7;

    public function storageKey(): string
    {
        return match ($this) {
            self::MONDAY => 'monday',
            self::TUESDAY => 'tuesday',
            self::WEDNESDAY => 'wednesday',
            self::THURSDAY => 'thursday',
            self::FRIDAY => 'friday',
            self::SATURDAY => 'saturday',
            self::SUNDAY => 'sunday',
        };
    }

    public function label(?string $locale = null, string $format = 'long'): string
    {
        $carbon = Carbon::now()->startOfWeek()->addDays($this->value - 1);
        $carbon->locale($locale ?? config('app.locale', 'en'));

        return match ($format) {
            'short' => $carbon->shortDayName,
            'min' => $carbon->minDayName,
            default => $carbon->dayName,
        };
    }

    public function key(): string
    {
        return strtolower($this->name);
    }

    public static function fromCarbon(Carbon $carbon): self
    {
        return self::from($carbon->dayOfWeekIso);
    }

    public static function weekdays(): array
    {
        return [self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY];
    }

    public static function weekend(): array
    {
        return [self::SATURDAY, self::SUNDAY];
    }

    public static function all(): array
    {
        return self::cases();
    }
}
