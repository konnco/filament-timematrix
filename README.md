# Filament Time Matrix

A Filament component that allows you to create an interactive time matrix for selecting time slots based on days and hours. Perfect for scheduling systems, availability management, and time slot selection.

## Features

- 📅 Interactive matrix for selecting hours by day
- ✅ Select all hours for a specific day
- ✅ Select all days for a specific hour
- ✅ Select all slots at once
- 🔄 Reset all selections
- 🔢 Selected slot counter
- 🛠️ Facade for data validation and manipulation
- 🎨 Customizable hours
- 🌍 **All day names powered by Carbon** - automatic multi-language support

## Requirements

- PHP 8.1 or higher
- Filament 3.0 or higher
- Laravel 10.0 or 11.0
- Carbon 2.0 or 3.0

## Installation

You can install the package via composer:

```bash
composer require konnco/filament-timematrix
```

## Basic Usage

### Simple Implementation

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

## Customization

### Customize Hours

```php
# Only 8 AM to 5 PM
TimeMatrix::make('schedule')
    ->hours(range(8, 17))
    ->label('Working Hours');

# Specific hours
TimeMatrix::make('schedule')
    ->hours([6, 7, 8, 9, 10, 14, 15, 16, 17, 18])
    ->label('Available Hours');
```

### Set Locale (Powered by Carbon)

```php
# Default locale (from config/app.php)
TimeMatrix::make('schedule')
    ->label('Schedule');

# Specific locale - Bahasa Indonesia
TimeMatrix::make('schedule')
    ->locale('id')
    ->label('Jadwal');

# Specific locale - French
TimeMatrix::make('schedule')
    ->locale('fr')
    ->label('Horaire');

# With custom format
TimeMatrix::make('schedule')
    ->locale('id', 'short') # Sen, Sel, Rab, Kam, Jum, Sab, Min
    ->label('Jadwal');
```

### Set Day Format

```php
# Change format only (keeps current locale)
TimeMatrix::make('schedule')
    ->dayFormat('short') # Mon, Tue, Wed, ...
    ->label('Schedule');
```

### Use Custom Day Keys

```php
# Custom keys with specific locale and format
TimeMatrix::make('schedule')
    ->dayKeys(
        ['mon', 'tue', 'wed', 'thu', 'fri'], # Only 5 days
        'id',    # locale
        'short'  # format
    )
    ->label('Jadwal Kerja');

# Single day
TimeMatrix::make('schedule')
    ->dayKeys(['monday'])
    ->locale('en')
    ->label('Monday Schedule');
```

### Helper Methods for Common Patterns

```php
# Weekdays only (Monday-Friday) with default keys
TimeMatrix::make('schedule')
    ->weekdaysOnly() # Set days first
    ->locale('id')   # Then set locale
    ->label('Jadwal Kerja');

# Weekdays with custom keys and locale in one call
TimeMatrix::make('schedule')
    ->weekdaysOnly(['sen', 'sel', 'rab', 'kam', 'jum'], 'id', 'short')
    ->label('Jadwal Kerja');

# Weekend only (Saturday-Sunday) with default keys
TimeMatrix::make('schedule')
    ->weekendOnly()  # Set days first
    ->locale('id')   # Then set locale
    ->label('Jadwal Akhir Pekan');

# Weekend with custom keys and locale in one call
TimeMatrix::make('schedule')
    ->weekendOnly(['sab', 'min'], 'id', 'short')
    ->label('Jadwal Akhir Pekan');
```

**Important:** When using helper methods like `weekdaysOnly()` or `weekendOnly()`:
- If you pass locale and format parameters directly to the helper, you're done
- If you chain with `->locale()`, make sure to call the helper method (weekdaysOnly/weekendOnly) BEFORE `->locale()`

### Hide Select All Buttons

```php
TimeMatrix::make('schedule')
    ->showSelectAllHours(false)
    ->showSelectAllDays(false);
```

### Complete Example

```php
use Konnco\FilamentTimeMatrix\Forms\TimeMatrix;

Forms\Components\Section::make('Schedule Settings')
    ->description('Configure your availability schedule')
    ->schema([
        TimeMatrix::make('working_hours')
            ->label('Working Hours')
            ->locale('id', 'long') # Indonesian locale with long format
            ->hours(range(8, 17)) # 8 AM to 5 PM
            ->required()
            ->helperText('Select your working hours for each day')
            ->showSelectAllHours(true)
            ->showSelectAllDays(true)
            ->columnSpanFull(),
    ]),
```

## Data Structure

The component stores data in the following format:

```php
[
    'monday' => [
        0 => false,
        1 => false,
        8 => true,  # Selected
        9 => true,  # Selected
        10 => true, # Selected
        # ... other hours
    ],
    'tuesday' => [
        # ... hours
    ],
    # ... other days
]
```

## Using the Facade

### Import Facade

```php
use Konnco\FilamentTimeMatrix\Facades\TimeMatrix;
```

### Validation

```php
# Validate data structure
$isValid = TimeMatrix::validate($scheduleData);

# Check if any slot is selected
$hasSelection = TimeMatrix::hasSelection($scheduleData);

# Count selected slots
$count = TimeMatrix::countChecked($scheduleData);
```

### Retrieve Data

```php
# Get all selected slots
$slots = TimeMatrix::getCheckedSlots($scheduleData);
# Returns: [['day' => 'monday', 'hour' => 8], ['day' => 'monday', 'hour' => 9], ...]

# Get days with selected slots
$days = TimeMatrix::getCheckedDays($scheduleData);
# Returns: ['monday', 'wednesday', 'friday']

# Get hours with selected slots
$hours = TimeMatrix::getCheckedHours($scheduleData);
# Returns: [8, 9, 10, 14, 15]
```

### Check Specific Slots

```php
# Check if specific slot is selected
$isSelected = TimeMatrix::isSlotChecked($scheduleData, 'monday', 8);

# Check if day has any selection
$hasDaySelection = TimeMatrix::hasDaySelection($scheduleData, 'monday');

# Check if hour has any selection
$hasHourSelection = TimeMatrix::hasHourSelection($scheduleData, 8);
```

### Format Output

```php
# Convert to readable format
$readable = TimeMatrix::toReadableFormat($scheduleData);
# Returns: ['monday' => [8, 9, 10], 'tuesday' => [14, 15]]

# Convert to human readable strings
$humanReadable = TimeMatrix::toHumanReadable($scheduleData);
# Returns: ['Monday: 08:00-08:59, 09:00-09:59, 10:00-10:59', ...]

# With custom day labels
$humanReadable = TimeMatrix::toHumanReadable($scheduleData, [
    'monday' => 'Senin',
    'tuesday' => 'Selasa',
    # ...
]);
# Returns: ['Senin: 08:00-08:59, 09:00-09:59', ...]
```

## Complete Facade Methods Reference

| Method | Description | Parameters | Return |
|--------|-------------|------------|--------|
| `validate($data)` | Validate data structure | `array $data` | `bool` |
| `hasSelection($data)` | Check if any slot is selected | `array $data` | `bool` |
| `getCheckedSlots($data)` | Get all selected slots | `array $data` | `array` |
| `isSlotChecked($data, $day, $hour)` | Check specific slot | `array $data, string $day, int $hour` | `bool` |
| `countChecked($data)` | Count selected slots | `array $data` | `int` |
| `getCheckedDays($data)` | Get days with selected slots | `array $data` | `array` |
| `getCheckedHours($data)` | Get hours with selected slots | `array $data` | `array` |
| `toReadableFormat($data)` | Format to readable array | `array $data` | `array` |
| `toHumanReadable($data, $labels)` | Format to readable strings | `array $data, array $labels = []` | `array` |
| `hasDaySelection($data, $day)` | Check if day has any slots | `array $data, string $day` | `bool` |
| `hasHourSelection($data, $hour)` | Check if hour has any slots | `array $data, int $hour` | `bool` |

## Practical Examples

### Display Schedule in Blade View

```blade
@php
    $schedule = $record->schedule; # Get from model
    $readable = \Konnco\FilamentTimeMatrix\Facades\TimeMatrix::toHumanReadable($schedule, [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ]);
@endphp

<div class="schedule-display">
    @foreach($readable as $daySchedule)
        <p>{{ $daySchedule }}</p>
    @endforeach
</div>
```

### Check Availability

```php
use Konnco\FilamentTimeMatrix\Facades\TimeMatrix;

public function isAvailable($schedule, $day, $hour): bool
{
    return TimeMatrix::isSlotChecked($schedule, $day, $hour);
}

# Usage
if ($this->isAvailable($record->schedule, 'monday', 9)) {
    # Available on Monday at 9 AM
}
```

### Validation in Form Request

```php
use Konnco\FilamentTimeMatrix\Facades\TimeMatrix;

public function rules()
{
    return [
        'schedule' => [
            'required',
            'array',
            function ($attribute, $value, $fail) {
                if (!TimeMatrix::validate($value)) {
                    $fail('The schedule format is invalid.');
                }
                
                if (!TimeMatrix::hasSelection($value)) {
                    $fail('Please select at least one time slot.');
                }
            },
        ],
    ];
}
```

## Credits

- [Konnco Studio](https://github.com/konnco)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
