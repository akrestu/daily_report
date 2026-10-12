<?php

namespace App\Support;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Kalender kerja layanan IT: hari kerja & jam kerja dari config/tickets.php, dikurangi hari libur.
 * Dipakai untuk auto-close tiket dan perhitungan SLA (menit jam kerja).
 */
class WorkingCalendar
{
    private static ?array $holidays = null;

    public static function flush(): void
    {
        static::$holidays = null;
    }

    public static function isWorkingDay(CarbonInterface $date): bool
    {
        return in_array($date->dayOfWeekIso, config('tickets.working_days'), true)
            && ! isset(static::holidays()[$date->toDateString()]);
    }

    /**
     * Tambah N hari kerja; hari non-kerja dilewati. Jam pada tanggal awal dipertahankan.
     */
    public static function addWorkingDays(CarbonInterface $from, int $days): CarbonInterface
    {
        $date = CarbonImmutable::instance($from);

        while ($days > 0) {
            $date = $date->addDay();
            if (static::isWorkingDay($date)) {
                $days--;
            }
        }

        return $date;
    }

    /**
     * Tambah N menit jam kerja. Contoh (jam kerja 06–17): Senin 16:30 + 60 menit = Selasa 06:30.
     */
    public static function addWorkingMinutes(CarbonInterface $from, int $minutes): CarbonImmutable
    {
        $cursor = CarbonImmutable::instance($from);

        for ($guard = 0; $guard < 3700; $guard++) {
            [$start, $end] = static::bounds($cursor);

            if (! static::isWorkingDay($cursor) || $cursor->gte($end)) {
                $cursor = static::bounds($cursor->addDay()->startOfDay())[0];

                continue;
            }

            if ($cursor->lt($start)) {
                $cursor = $start;
            }

            $available = (int) $cursor->diffInMinutes($end);
            if ($minutes <= $available) {
                return $cursor->addMinutes($minutes);
            }

            $minutes -= $available;
            $cursor = static::bounds($cursor->addDay()->startOfDay())[0];
        }

        throw new \RuntimeException('Tidak ada hari kerja dalam 10 tahun ke depan. Periksa config tickets.working_days.');
    }

    /**
     * Jumlah menit jam kerja di antara dua waktu (0 jika $to <= $from).
     */
    public static function workingMinutesBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        $from = CarbonImmutable::instance($from);
        $to = CarbonImmutable::instance($to);
        $total = 0;

        for ($day = $from->startOfDay(); $day->lte($to); $day = $day->addDay()) {
            if (! static::isWorkingDay($day)) {
                continue;
            }

            [$start, $end] = static::bounds($day);
            $overlapStart = $from->max($start);
            $overlapEnd = $to->min($end);

            if ($overlapEnd->gt($overlapStart)) {
                $total += (int) $overlapStart->diffInMinutes($overlapEnd);
            }
        }

        return $total;
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} jam mulai & selesai kerja pada tanggal tsb */
    private static function bounds(CarbonInterface $date): array
    {
        $date = CarbonImmutable::instance($date);
        [$sh, $sm] = array_map('intval', explode(':', config('tickets.working_hours.start')));
        [$eh, $em] = array_map('intval', explode(':', config('tickets.working_hours.end')));

        return [$date->setTime($sh, $sm), $date->setTime($eh, $em)];
    }

    private static function holidays(): array
    {
        return static::$holidays ??= Holiday::pluck('date')
            ->mapWithKeys(fn ($date) => [CarbonImmutable::parse($date)->toDateString() => true])
            ->all();
    }
}
