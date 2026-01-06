<?php

namespace Konnco\FilamentTimeMatrix\Forms;

use Filament\Forms\Components\Field;
use Carbon\Carbon;
use Konnco\FilamentTimeMatrix\Enums\Day;

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

        $this->carbonLocale = config('app.locale', 'en');

        $this->days = $this->generateDays();

        $this->default([]);

        $this->dehydrated();

        $this->addValidationRule();
    }

    protected function addValidationRule(): void
    {
        $this->rule(function () {
            return function (string $attribute, $value, \Closure $fail) {
                if (!$this->isRequired()) {
                    return;
                }

                if (!is_array($value) || empty($value)) {
                    $fail('Please select at least one time slot.');
                    return;
                }

                $hasSelection = false;
                foreach ($value as $hours) {
                    if (is_array($hours) && in_array(true, $hours, true)) {
                        $hasSelection = true;
                        break;
                    }
                }

                if (!$hasSelection) {
                    $fail('Please select at least one time slot.');
                }
            };
        });
    }

    /**
     * Set hours range (supports business hours, custom ranges)
     */
    public function hours(int $startTime, int $endTime): static
    {
        if ($startTime < 0 || $startTime > 23 || $endTime < 0 || $endTime > 23 || $startTime > $endTime) {
            throw new \InvalidArgumentException('Hours must be 0-23, startTime ≤ endTime.');
        }

        $this->hours = range($startTime, $endTime);

        return $this;
    }

    /**
     * Business hours shortcut
     */
    public function businessHours(int $startTime = 9, int $endTime = 17): static
    {
        return $this->hours($startTime, $endTime);
    }

    /**
     * Set locale and regenerate day labels
     */
    public function locale(?string $locale = null, string $format = 'long'): static
    {
        $this->carbonLocale = $locale ?? config('app.locale', 'en');

        $this->carbonFormat = $format;

        $this->days = $this->generateDays();

        return $this;
    }

    /**
     * Set specific days using Day enum
     */
    public function days(array $days): static
    {
        $processedDays = [];

        foreach ($days as $day) {
            if ($day instanceof Day) {
                $key = $day->key();
                $processedDays[$key] = $day->label($this->carbonLocale, $this->carbonFormat);
            }
        }

        $this->days = $processedDays;

        return $this;
    }

    /**
     * Weekdays only (Mon-Fri)
     */
    public function weekdays(): static
    {
        return $this->days(Day::weekdays());
    }

    /**
     * Weekend only (Sat-Sun)
     */
    public function weekend(): static
    {
        return $this->days(Day::weekend());
    }

    /**
     * Generate all days with current locale/format
     */
    protected function generateDays(): array
    {
        $days = [];
        $allDays = [Day::MONDAY, Day::TUESDAY, Day::WEDNESDAY, Day::THURSDAY, Day::FRIDAY, Day::SATURDAY, Day::SUNDAY];

        foreach ($allDays as $day) {
            $key = $day->key();

            $days[$key] = $day->label($this->carbonLocale, $this->carbonFormat);
        }

        return $days;
    }

    public function getHours(): array
    {
        return $this->hours;
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
