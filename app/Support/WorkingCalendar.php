<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Kalender hari kerja layanan IT (default Senin–Sabtu, lihat config/tickets.php).
 */
class WorkingCalendar
{
    public static function isWorkingDay(CarbonInterface $date): bool
    {
        return in_array($date->dayOfWeekIso, config('tickets.working_days'), true);
    }

    /**
     * Tambah N hari kerja; hari non-kerja dilewati. Jam pada tanggal awal dipertahankan.
     */
    public static function addWorkingDays(CarbonInterface $from, int $days): CarbonInterface
    {
        $date = $from->copy();

        while ($days > 0) {
            $date = $date->addDay();
            if (static::isWorkingDay($date)) {
                $days--;
            }
        }

        return $date;
    }
}
