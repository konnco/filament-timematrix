<?php

namespace Konnco\FilamentTimeMatrix\Services;

use Carbon\Carbon;
use Konnco\FilamentTimeMatrix\Enums\Day;

class TimeMatrixValidator
{
    /**
     * Validate whether the time matrix data structure is valid
     */
    public function validate(array $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        foreach ($data as $day => $times) {
            if (!is_array($times)) {
                return false;
            }

            foreach ($times as $time => $checked) {
                if (!is_bool($checked)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if there is at least one selected slot
     */
    public function hasSelection(array $data): bool
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        foreach ($data as $times) {
            if (is_array($times)) {
                foreach ($times as $checked) {
                    if ($checked === true) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Convert data into a more readable array format
     * Format: ['monday' => [8, 9, 10], 'tuesday' => [14, 15]]
     */
    public function toReadableFormat(array $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $readable = [];

        foreach ($data as $day => $times) {
            if (!is_array($times)) {
                continue;
            }

            $checkedHours = [];

            foreach ($times as $hour => $checked) {
                if ($checked === true) {
                    $checkedHours[] = (int) $hour;
                }
            }

            if (!empty($checkedHours)) {
                sort($checkedHours);
                $readable[$day] = $checkedHours;
            }
        }

        return $readable;
    }

    /**
     * Check if a specific Carbon date/time is selected
     */
    public function isDateTimeChecked(array $data, Carbon $dateTime): bool
    {
        $dayKey = strtolower($dateTime->englishDayOfWeek);
        $hour = $dateTime->hour;

        return isset($data[$dayKey][$hour]) && $data[$dayKey][$hour] === true;
    }

    /**
     * Check if time slot is active/available at specific date/time
     * Defaults to current time if no dateTime provided
     *
     * Usage:
     * - isActiveAt($data) // Check now
     * - isActiveAt($data, now()) // Check now (explicit)
     * - isActiveAt($data, Carbon::parse('2024-01-15 14:00')) // Check specific time
     */
    public function isActiveAt(array $data, ?Carbon $dateTime = null): bool
    {
        if (!$this->validate($data)) {
            return false;
        }

        return $this->isDateTimeChecked($data, $dateTime ?? Carbon::now());
    }

    /**
     * Check if there are any active slots on a specific day
     * Defaults to today if no day provided
     *
     * Usage:
     * - hasActiveDay($data) // Check today
     * - hasActiveDay($data, Day::MONDAY) // Check Monday
     * - hasActiveDay($data, 'monday') // Check Monday (string)
     * - hasActiveDay($data, Day::fromCarbon(Carbon::tomorrow())) // Check tomorrow
     */
    public function hasActiveDay(array $data, string|Day|null $day = null): bool
    {
        if (!$this->validate($data)) {
            return false;
        }

        if ($day === null) {
            $day = Day::fromCarbon(Carbon::today());
        }

        $dayKey = $day instanceof Day ? $day->key() : strtolower($day);

        if (!isset($data[$dayKey]) || !is_array($data[$dayKey])) {
            return false;
        }

        foreach ($data[$dayKey] as $checked) {
            if ($checked === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all active hours for a specific day
     * Defaults to today if no day provided
     * Returns array of hours [9, 10, 11, 14, 15] or empty array
     *
     * Usage:
     * - getActiveHours($data) // Get today's hours
     * - getActiveHours($data, Day::MONDAY) // Get Monday's hours
     * - getActiveHours($data, 'monday') // Get Monday's hours (string)
     * - getActiveHours($data, Day::fromCarbon(Carbon::tomorrow())) // Get tomorrow's hours
     */
    public function getActiveHours(array $data, string|Day|null $day = null): array
    {
        if (!$this->validate($data)) {
            return [];
        }

        if ($day === null) {
            $day = Day::fromCarbon(Carbon::today());
        }

        $dayKey = $day instanceof Day ? $day->key() : strtolower($day);
        $readable = $this->toReadableFormat($data);

        return $readable[$dayKey] ?? [];
    }

    /**
     * Check if all time slots are active (24/7)
     */
    public function isFullyActive(array $data): bool
    {
        $readable = $this->toReadableFormat($data);

        if (count($readable) !== 7) {
            return false;
        }

        foreach ($readable as $hours) {
            if (count($hours) !== 24) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total active hours per week
     */
    public function getTotalActiveHours(array $data): int
    {
        $readable = $this->toReadableFormat($data);
        $total = 0;

        foreach ($readable as $hours) {
            $total += count($hours);
        }

        return $total;
    }

    /**
     * Get days that have at least one active hour
     * Returns array of Day enums
     */
    public function getActiveDays(array $data): array
    {
        $readable = $this->toReadableFormat($data);
        $activeDays = [];

        foreach ($readable as $dayKey => $hours) {
            if (!empty($hours)) {
                foreach (Day::all() as $day) {
                    if ($day->key() === $dayKey) {
                        $activeDays[] = $day;
                        break;
                    }
                }
            }
        }

        return $activeDays;
    }

    /**
     * Get percentage of active hours in a week (0-100)
     */
    public function getActivePercentage(array $data): float
    {
        $totalPossible = 7 * 24;
        $totalActive = $this->getTotalActiveHours($data);

        return round(($totalActive / $totalPossible) * 100, 2);
    }

    /**
     * Check if specific day and hour is active
     */
    public function isDayHourActive(array $data, string|Day $day, int $hour): bool
    {
        if ($hour < 0 || $hour > 23) {
            return false;
        }

        $dayKey = $day instanceof Day ? $day->key() : strtolower($day);

        return isset($data[$dayKey][$hour]) && $data[$dayKey][$hour] === true;
    }

    /**
     * Get next available slot from a given Carbon instance
     */
    public function getNextAvailableSlot(array $data, Carbon $from): ?Carbon
    {
        $readableData = $this->toReadableFormat($data);
        $current = $from->copy();

        for ($i = 0; $i < 7; $i++) {
            $dayKey = strtolower($current->englishDayOfWeek);

            if (isset($readableData[$dayKey])) {
                foreach ($readableData[$dayKey] as $hour) {
                    $slot = $current->copy()->setTime($hour, 0, 0);

                    if ($slot->isAfter($from)) {
                        return $slot;
                    }
                }
            }

            $current->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Get all available slots within a date range
     */
    public function getSlotsInRange(array $data, Carbon $start, Carbon $end): array
    {
        $slots = [];
        $readableData = $this->toReadableFormat($data);
        $current = $start->copy()->startOfDay();

        while ($current->lte($end)) {
            $dayKey = strtolower($current->englishDayOfWeek);

            if (isset($readableData[$dayKey])) {
                foreach ($readableData[$dayKey] as $hour) {
                    $slot = $current->copy()->setTime($hour, 0, 0);

                    if ($slot->between($start, $end)) {
                        $slots[] = $slot;
                    }
                }
            }

            $current->addDay();
        }

        return $slots;
    }

    /**
     * Convert data to Carbon instances for a specific week
     */
    public function toCarbonSlots(array $data, ?Carbon $referenceWeek = null): array
    {
        $reference = $referenceWeek ?? Carbon::now();
        $readableData = $this->toReadableFormat($data);
        $slots = [];

        foreach ($readableData as $day => $hours) {
            $dayConstant = $this->getDayConstant($day);

            if ($dayConstant === null) {
                continue;
            }

            $targetDate = $reference->copy()->startOfWeek()->addDays($dayConstant - 1);

            foreach ($hours as $hour) {
                $slots[] = $targetDate->copy()->setTime($hour, 0, 0);
            }
        }

        return $slots;
    }

    /**
     * Format day schedule to human readable string with grouped consecutive hours
     * Example: "09:00-11:59, 14:00-16:59"
     */
    public function formatDaySchedule(array $data, string|Day $day): string
    {
        $dayKey = $day instanceof Day ? $day->key() : $day;
        $readable = $this->toReadableFormat($data);

        if (!isset($readable[$dayKey]) || empty($readable[$dayKey])) {
            return '';
        }

        $hours = $readable[$dayKey];
        sort($hours);

        $groups = [];
        $start = null;
        $end = null;

        foreach ($hours as $hour) {
            if ($start === null) {
                $start = $hour;
                $end = $hour;
            } elseif ($hour === $end + 1) {
                $end = $hour;
            } else {
                $groups[] = $start === $end
                    ? sprintf('%02d:00-%02d:59', $start, $start)
                    : sprintf('%02d:00-%02d:59', $start, $end);

                $start = $hour;
                $end = $hour;
            }
        }

        if ($start !== null) {
            $groups[] = $start === $end
                ? sprintf('%02d:00-%02d:59', $start, $start)
                : sprintf('%02d:00-%02d:59', $start, $end);
        }

        return implode(', ', $groups);
    }

    /**
     * Format all days schedule to human readable array
     * Returns: ['Monday' => '09:00-11:59, 14:00-16:59', 'Tuesday' => ...]
     */
    public function formatAllDays(array $data, ?string $locale = null): array
    {
        $readable = $this->toReadableFormat($data);
        $result = [];

        foreach (Day::all() as $day) {
            $dayKey = $day->key();

            if (!isset($readable[$dayKey]) || empty($readable[$dayKey])) {
                continue;
            }

            $dayLabel = $day->label($locale);
            $result[$dayLabel] = $this->formatDaySchedule($data, $day);
        }

        return $result;
    }

    /**
     * Get Carbon day constant from day key
     */
    protected function getDayConstant(string $day): ?int
    {
        return match ($day) {
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
            default => null,
        };
    }
}
