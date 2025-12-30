<?php

namespace Konnco\FilamentTimeMatrix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool validate($data)
 * @method static bool hasSelection($data)
 * @method static array getCheckedSlots($data)
 * @method static bool isSlotChecked($data, string $day, int $hour)
 * @method static int countChecked($data)
 * @method static array getCheckedDays($data)
 * @method static array getCheckedHours($data)
 * @method static array toReadableFormat($data)
 * @method static array toHumanReadable($data, array $dayLabels = [])
 * @method static bool hasDaySelection($data, string $day)
 * @method static bool hasHourSelection($data, int $hour)
 *
 * @see \Konnco\FilamentTimeMatrix\Services\TimeMatrixValidator
 */
class TimeMatrix extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-timematrix.validator';
    }
}
