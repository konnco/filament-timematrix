<?php

namespace Konnco\FilamentTimeMatrix\Facades;

use Illuminate\Support\Facades\Facade;
use Carbon\Carbon;
use Konnco\FilamentTimeMatrix\Enums\Day;

/**
 * @method static bool validate(array $data)
 * @method static bool hasSelection(array $data)
 * @method static array toReadableFormat(array $data)
 * @method static bool isDateTimeChecked(array $data, Carbon $dateTime)
 * @method static bool isActiveAt(array $data, ?Carbon $dateTime = null)
 * @method static bool hasActiveDay(array $data, string|Day|null $day = null)
 * @method static array getActiveHours(array $data, string|Day|null $day = null)
 * @method static bool isFullyActive(array $data)
 * @method static int getTotalActiveHours(array $data)
 * @method static array getActiveDays(array $data)
 * @method static float getActivePercentage(array $data)
 * @method static bool isDayHourActive(array $data, string|Day $day, int $hour)
 * @method static Carbon|null getNextAvailableSlot(array $data, Carbon $from)
 * @method static array getSlotsInRange(array $data, Carbon $start, Carbon $end)
 * @method static array toCarbonSlots(array $data, ?Carbon $referenceWeek = null)
 * @method static string formatDaySchedule(array $data, string|Day $day)
 * @method static array formatAllDays(array $data, ?string $locale = null)
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
