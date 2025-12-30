<?php

namespace Konnco\FilamentTimeMatrix\Services;

class TimeMatrixValidator
{
    /**
     * Validate whether the time matrix data structure is valid
     */
    public function validate($data): bool
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
    public function hasSelection($data): bool
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
     * Get all selected slots
     */
    public function getCheckedSlots($data): array
    {
        $checked = [];

        if (!is_array($data)) {
            return $checked;
        }

        foreach ($data as $day => $times) {
            if (!is_array($times)) {
                continue;
            }

            foreach ($times as $hour => $isChecked) {
                if ($isChecked === true) {
                    $checked[] = [
                        'day' => $day,
                        'hour' => $hour,
                    ];
                }
            }
        }

        return $checked;
    }

    /**
     * Check whether a specific day and hour slot is selected
     */
    public function isSlotChecked($data, string $day, int $hour): bool
    {
        return isset($data[$day][$hour]) && $data[$day][$hour] === true;
    }

    /**
     * Count total selected slots
     */
    public function countChecked($data): int
    {
        if (!is_array($data)) {
            return 0;
        }

        $count = 0;

        foreach ($data as $times) {
            if (is_array($times)) {
                foreach ($times as $checked) {
                    if ($checked === true) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Get list of days that have at least one selected slot
     */
    public function getCheckedDays($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $days = [];

        foreach ($data as $day => $times) {
            if (is_array($times)) {
                foreach ($times as $checked) {
                    if ($checked === true && !in_array($day, $days)) {
                        $days[] = $day;
                        break;
                    }
                }
            }
        }

        return $days;
    }

    /**
     * Get list of hours that have at least one selected slot
     */
    public function getCheckedHours($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $hours = [];

        foreach ($data as $dayTimes) {
            if (is_array($dayTimes)) {
                foreach ($dayTimes as $hour => $checked) {
                    if ($checked === true && !in_array($hour, $hours)) {
                        $hours[] = (int) $hour;
                    }
                }
            }
        }

        sort($hours);

        return $hours;
    }

    /**
     * Convert data into a more readable array format
     * Format: ['monday' => [8, 9, 10], 'tuesday' => [14, 15]]
     */
    public function toReadableFormat($data): array
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
     * Convert data into a human-readable string format
     * Format: ['Monday: 08:00-08:59, 09:00-09:59', 'Tuesday: 14:00-14:59']
     */
    public function toHumanReadable($data, array $dayLabels = []): array
    {
        $readable = $this->toReadableFormat($data);
        $result = [];

        $defaultLabels = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];

        $labels = !empty($dayLabels) ? $dayLabels : $defaultLabels;

        foreach ($readable as $day => $hours) {
            $dayLabel = $labels[$day] ?? ucfirst($day);
            $hourStrings = array_map(function ($hour) {
                return sprintf('%02d:00-%02d:59', $hour, $hour);
            }, $hours);

            $result[] = $dayLabel . ': ' . implode(', ', $hourStrings);
        }

        return $result;
    }

    /**
     * Check whether a specific day has at least one selected slot
     */
    public function hasDaySelection($data, string $day): bool
    {
        if (!isset($data[$day]) || !is_array($data[$day])) {
            return false;
        }

        foreach ($data[$day] as $checked) {
            if ($checked === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether a specific hour has at least one selected slot on any day
     */
    public function hasHourSelection($data, int $hour): bool
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $times) {
            if (isset($times[$hour]) && $times[$hour] === true) {
                return true;
            }
        }

        return false;
    }
}
