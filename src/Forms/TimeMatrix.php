<?php

namespace Konnco\FilamentTimeMatrix\Forms;

use Filament\Forms\Components\Field;

class TimeMatrix extends Field
{
    protected string $view = 'filament-timematrix::time-matrix';

    protected array $hours = [];

    protected array $days = [];

    protected bool $showSelectAllHours = true;

    protected bool $showSelectAllDays = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hours = range(0, 23);

        $this->days = [
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        ];

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

    public function days(array $days): static
    {
        $this->days = $days;

        return $this;
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
