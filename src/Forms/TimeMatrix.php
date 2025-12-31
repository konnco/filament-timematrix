<?php

namespace Konnco\FilamentTimeMatrix\Forms;

use Filament\Forms\Components\Field;
use Carbon\Carbon;

class TimeMatrix extends Field
{
    protected string $view = 'filament-timematrix::time-matrix';

    protected array $hours = [];

    protected array $days = [];

    protected bool $showSelectAllHours = true;

    protected bool $showSelectAllDays = true;

    protected ?string $carbonLocale = null;

    protected string $carbonFormat = 'long';

    protected function setUp(): void
    {
        parent::setUp();

        $this->hours = range(0, 23);

        $this->days = $this->generateCarbonDays(
            config('app.locale', 'en'),
            'long'
        );

        $this->carbonLocale = config('app.locale', 'en');

        $this->default([]);

        $this->dehydrated();

        $this->rule(function () {
            return function (string $attribute, $value, \Closure $fail) {
                if ($this->isRequired()) {

                    if (!is_array($value) || empty($value)) {
                        $fail('Please select at least one time slot.');

                        return;
                    }

                    $hasSelection = false;

                    foreach ($value as $day => $hours) {
                        if (is_array($hours)) {
                            foreach ($hours as $hour => $isSelected) {
                                if ($isSelected === true) {
                                    $hasSelection = true;

                                    break 2;
                                }
                            }
                        }
                    }

                    if (!$hasSelection) {
                        $fail('Please select at least one time slot.');
                    }
                }
            };
        });
    }

    public function hours(array $hours): static
    {
        $this->hours = $hours;

        return $this;
    }

    public function getHours(): array
    {
        return $this->hours;
    }

    /**
     * Set locale for day names (uses Carbon)
     * Will regenerate day names with new locale
     *
     * @param string|null $locale Locale code (e.g., 'id', 'en', 'fr')
     * @param string $format Day name format: 'long' (default), or 'short'
     */
    public function locale(?string $locale = null, string $format = 'long'): static
    {
        $this->carbonLocale = $locale ?? config('app.locale', 'en');

        $this->carbonFormat = $format;

        $this->regenerateDaysWithNewLocale();

        return $this;
    }

    /**
     * Regenerate existing days with current locale and format
     */
    protected function regenerateDaysWithNewLocale(): void
    {
        $locale = $this->carbonLocale ?? config('app.locale', 'en');

        $format = $this->carbonFormat;

        $newDays = [];

        $dayIndexMap = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        foreach ($this->days as $key => $value) {
            if (isset($dayIndexMap[$key])) {
                $carbon = Carbon::now()->startOfWeek()->addDays($dayIndexMap[$key]);

                $carbon->locale($locale);

                $dayName = match ($format) {
                    'short' => $carbon->shortDayName,
                    default => $carbon->dayName,
                };

                $newDays[$key] = $dayName;
            }
        }

        $this->days = $newDays;
    }

    /**
     * Set day name format
     *
     * @param string $format 'long', or 'short'
     */
    public function dayFormat(string $format): static
    {
        $this->carbonFormat = $format;

        $this->days = $this->generateCarbonDays(
            $this->carbonLocale ?? config('app.locale', 'en'),

            $format
        );

        return $this;
    }

    /**
     * Set custom day keys with Carbon translation
     * Supports 1-7 days for flexible scheduling
     *
     * @param array $dayKeys Custom day keys (e.g., ['mon', 'tue', 'wed', 'thu', 'fri'] for weekdays only)
     * @param string|null $locale Optional locale
     * @param string $format Day name format
     */
    public function dayKeys(array $dayKeys, ?string $locale = null, string $format = 'long'): static
    {
        $count = count($dayKeys);

        if ($count < 1 || $count > 7) {
            throw new \InvalidArgumentException('Day keys must contain between 1 and 7 elements.');
        }

        $locale = $locale ?? $this->carbonLocale ?? config('app.locale', 'en');

        $days = [];

        foreach ($dayKeys as $index => $key) {
            $carbon = Carbon::now()->startOfWeek()->addDays($index);

            $carbon->locale($locale);

            $dayName = match ($format) {
                'short' => $carbon->shortDayName,
                default => $carbon->dayName,
            };

            $days[$key] = $dayName;
        }

        $this->days = $days;

        return $this;
    }

    /**
     * Helper method to quickly set weekdays only (Monday-Friday)
     *
     * @param array|null $customKeys Optional custom keys for weekdays
     * @param string|null $locale Optional locale
     * @param string $format Day name format
     */
    public function weekdaysOnly(?array $customKeys = null, ?string $locale = null, string $format = 'long'): static
    {
        $keys = $customKeys ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return $this->dayKeys($keys, $locale, $format);
    }

    /**
     * Helper method to quickly set weekend only (Saturday-Sunday)
     *
     * @param array|null $customKeys Optional custom keys for weekend
     * @param string|null $locale Optional locale
     * @param string $format Day name format
     */
    public function weekendOnly(?array $customKeys = null, ?string $locale = null, string $format = 'long'): static
    {
        $keys = $customKeys ?? ['saturday', 'sunday'];

        $locale = $locale ?? $this->carbonLocale ?? config('app.locale', 'en');

        $days = [];

        foreach ($keys as $index => $key) {
            $carbon = Carbon::now()->startOfWeek()->addDays(5 + $index);

            $carbon->locale($locale);

            $dayName = match ($format) {
                'short' => $carbon->shortDayName,
                default => $carbon->dayName,
            };

            $days[$key] = $dayName;
        }

        $this->days = $days;

        return $this;
    }

    /**
     * Generate days array using Carbon
     */
    protected function generateCarbonDays(string $locale, string $format = 'long'): array
    {
        $days = [];

        $dayKeys = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($dayKeys as $index => $key) {
            $carbon = Carbon::now()->startOfWeek()->addDays($index);

            $carbon->locale($locale);

            $dayName = match ($format) {
                'short' => $carbon->shortDayName,
                default => $carbon->dayName,
            };

            $days[$key] = $dayName;
        }

        return $days;
    }

    public function getDays(): array
    {
        return $this->days;
    }

    public function showSelectAllHours(bool $show = true): static
    {
        $this->showSelectAllHours = $show;

        return $this;
    }

    public function getShowSelectAllHours(): bool
    {
        return $this->showSelectAllHours;
    }

    public function showSelectAllDays(bool $show = true): static
    {
        $this->showSelectAllDays = $show;

        return $this;
    }

    public function getShowSelectAllDays(): bool
    {
        return $this->showSelectAllDays;
    }

    public function getState(): mixed
    {
        $state = parent::getState();

        if (empty($state)) {
            return [];
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($state) ? $state : [];
    }
}
