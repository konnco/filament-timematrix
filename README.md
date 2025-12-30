# Filament Time Matrix

A Filament component that allows you to create an interactive time matrix for selecting time slots based on days and hours. Perfect for scheduling systems, and time availability management.

## Features

- Interactive matrix for selecting hours by day
- Select all hours for a specific day
- Select all days for a specific hour
- Select all slots at once
- Reset all selections
- Selected slot counter
- Facade for data validation and manipulation
- Customizable days and hours

## Installation

You can install the package via composer:
```bash
composer require konnco/filament-timematrix
```

## Basic Usage

### In Filament Resource
```php
use Konnco\FilamentTimeMatrix\Forms\TimeMatrix;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TimeMatrix::make('schedule')
                ->label('Select Schedule')
                ->required()
                ->helperText('Select operational hours for each day'),
        ]);
}
```

### Customize Hours
```php
// Only 8 AM to 5 PM

TimeMatrix::make('schedule')
    ->hours(range(8, 17))
    ->label('Working Hours');
```

### Customize Days
```php
TimeMatrix::make('schedule')
    ->days([
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
    ])
    ->label('Working Days');
```

### Hide Select All Buttons
```php
TimeMatrix::make('schedule')
    ->showSelectAllHours(false)
    ->showSelectAllDays(false);
```

## Complete Facade Methods

| Method | Description | Return |
|--------|-------------|--------|
| `validate($data)` | Validate data structure | `bool` |
| `hasSelection($data)` | Check if any slot is selected | `bool` |
| `getCheckedSlots($data)` | Get all selected slots | `array` |
| `isSlotChecked($data, $day, $hour)` | Check specific slot | `bool` |
| `countChecked($data)` | Count selected slots | `int` |
| `getCheckedDays($data)` | Get days with selected slots | `array` |
| `getCheckedHours($data)` | Get hours with selected slots | `array` |
| `toReadableFormat($data)` | Format to readable array | `array` |
| `toHumanReadable($data, $labels)` | Format to readable strings | `array` |
| `hasDaySelection($data, $day)` | Check if day has any slots | `bool` |
| `hasHourSelection($data, $hour)` | Check if hour has any slots | `bool` |



## Credits

- [Konnco Studio](https://github.com/konnco)
- [All Contributors](../../contributors)
