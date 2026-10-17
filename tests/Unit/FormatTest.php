<?php

namespace Tests\Unit;

use App\Support\Format;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FormatTest extends TestCase
{
    public function test_formats_dates_in_indonesian_short_month(): void
    {
        $date = Carbon::create(2026, 10, 7, 14, 30, 0, 'Asia/Jakarta');

        $this->assertSame('7 Okt 2026', Format::date($date));
        $this->assertSame('7 Okt 2026, 14:30', Format::datetime($date));
        $this->assertSame('-', Format::datetime(null));
    }

    public function test_converts_utc_input_to_wib(): void
    {
        $this->assertSame('1 Agu 2026, 07:00', Format::datetime(Carbon::create(2026, 8, 1, 0, 0, 0, 'UTC')));
    }

    public function test_formats_numbers_with_indonesian_separators(): void
    {
        $this->assertSame('1.248', Format::number(1248));
        $this->assertSame('91,4%', Format::percent(91.43));
    }

    public function test_formats_short_durations(): void
    {
        $this->assertSame('45m', Format::duration(45));
        $this->assertSame('5j 40m', Format::duration(340));
        $this->assertSame('2j', Format::duration(120));
    }
}
