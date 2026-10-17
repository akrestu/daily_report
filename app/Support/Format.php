<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Format tampilan sesuai design system SiGAP (WIB, Bahasa Indonesia).
 * Contoh: 17 Okt 2026, 14:30 · 1.248 · 91,4%
 */
class Format
{
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public static function date(CarbonInterface|string|null $value): string
    {
        $date = self::toCarbon($value);

        return $date ? $date->day.' '.self::MONTHS[$date->month - 1].' '.$date->year : '-';
    }

    /** "17 Okt" — untuk label grafik & kartu ringkas */
    public static function dayMonth(CarbonInterface|string|null $value): string
    {
        $date = self::toCarbon($value);

        return $date ? $date->day.' '.self::MONTHS[$date->month - 1] : '-';
    }

    /** "Sabtu, 17 Okt 2026" */
    public static function longDate(CarbonInterface|string|null $value): string
    {
        $date = self::toCarbon($value);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $date ? $days[$date->dayOfWeek].', '.self::date($date) : '-';
    }

    public static function datetime(CarbonInterface|string|null $value): string
    {
        $date = self::toCarbon($value);

        return $date ? self::date($date).', '.$date->format('H:i') : '-';
    }

    public static function number(int|float|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function percent(int|float|null $value, int $decimals = 1): string
    {
        return self::number($value, $decimals).'%';
    }

    /** Durasi ringkas dari menit: "45m", "5j 40m", "2j". */
    public static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        if ($hours === 0) {
            return "{$rest}m";
        }

        return $rest === 0 ? "{$hours}j" : "{$hours}j {$rest}m";
    }

    private static function toCarbon(CarbonInterface|string|null $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof CarbonInterface ? $value : Carbon::parse($value);

        return $date->copy()->setTimezone(config('app.timezone'));
    }
}
