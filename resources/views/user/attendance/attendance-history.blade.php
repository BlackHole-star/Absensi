@extends('layouts.app')

@section('content')

<div class="container-fluid px-2 px-md-4">

    {{-- Judul --}}
    <div class="mb-3">
        <h4 class="fw-bold mb-1">Riwayat Absensi</h4>
        <small class="text-muted">Riwayat kehadiran berdasarkan bulan</small>
    </div>

    {{-- Filter Bulan --}}
    <form method="GET" class="row g-2 mb-4 align-items-end">

        <div class="col-12 col-md-4">
            <label for="month" class="form-label fw-semibold">
                Pilih Bulan
            </label>

            <input
                type="month"
                name="month"
                id="month"
                class="form-control"
                value="{{ request('month', now()->format('Y-m')) }}"
                max="{{ now()->format('Y-m') }}"
                required
            >
        </div>

        <div class="col-12 col-md-2">
            <button
                type="submit"
                class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2"
            >
                <i class="bi bi-funnel"></i>
                <span>Tampilkan</span>
            </button>
        </div>

    </form>


    @php

        use Carbon\Carbon;

        /*
        |--------------------------------------------------------------------------
        | Bulan yang dipilih
        |--------------------------------------------------------------------------
        */

        $selectedMonth = request('month', now()->format('Y-m'));

        try {
            $start = Carbon::createFromFormat('Y-m-d', $selectedMonth . '-01')
                ->startOfMonth();
        } catch (\Exception $e) {
            $start = now()->startOfMonth();
        }

        $end = $start->copy()->endOfMonth();


        /*
        |--------------------------------------------------------------------------
        | Mapping data absensi berdasarkan tanggal
        |--------------------------------------------------------------------------
        */

        $attMap = collect($attendances ?? [])
            ->filter(function ($attendance) {
                return !empty($attendance->tanggal_absensi);
            })
            ->keyBy(function ($attendance) {

                return Carbon::parse(
                    $attendance->tanggal_absensi
                )->format('Y-m-d');

            });


        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $presentCount = 0;
        $lateCount = 0;
        $excusedCount = 0;
        $sickCount = 0;
        $absentCount = 0;


        /*
        |--------------------------------------------------------------------------
        | Mulai kalender dari hari Senin
        |--------------------------------------------------------------------------
        */

        $current = $start->copy()->startOfWeek(Carbon::MONDAY);

    @endphp


    {{-- ========================= --}}
    {{-- KALENDER --}}
    {{-- ========================= --}}

    <div class="calendar-wrapper">

        {{-- Header Hari --}}
        <div class="calendar-grid calendar-head-row">

            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)

                <div class="calendar-head">
                    {{ $day }}
                </div>

            @endforeach

        </div>


        {{-- Isi Kalender --}}
        <div class="calendar-grid">

            @while ($current <= $end)

                @for ($i = 0; $i < 7; $i++)

                    @php

                        $dateStr = $current->format('Y-m-d');

                        $dayOfWeek = $current->dayOfWeek;

                        /*
                        |--------------------------------------------------------------------------
                        | Cek apakah tanggal berada di bulan & tahun yang dipilih
                        |--------------------------------------------------------------------------
                        */

                        $isInMonth =
                            $current->month == $start->month &&
                            $current->year == $start->year;


                        /*
                        |--------------------------------------------------------------------------
                        | Cek weekend
                        |--------------------------------------------------------------------------
                        */

                        $isSaturday = $dayOfWeek === Carbon::SATURDAY;
                        $isSunday = $dayOfWeek === Carbon::SUNDAY;
                        $isWeekend = $isSaturday || $isSunday;


                        /*
                        |--------------------------------------------------------------------------
                        | Ambil data absensi
                        |--------------------------------------------------------------------------
                        */

                        $att = $attMap[$dateStr] ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | Default status
                        |--------------------------------------------------------------------------
                        */

                        $status = '-';
                        $short = '-';
                        $class = 'status-solid-none';


                        /*
                        |--------------------------------------------------------------------------
                        | Hari kerja dalam bulan
                        |--------------------------------------------------------------------------
                        */

                        if ($isInMonth && !$isWeekend) {

                            if ($att) {

                                $status = strtolower(
                                    trim($att->status_kehadiran ?? '')
                                );

                            } else {

                                /*
                                Jika tanggal sudah lewat dan tidak ada
                                data absensi maka dianggap alpha.
                                */

                                if ($current->isBefore(now()->startOfDay())) {
                                    $status = 'alpha';
                                } else {
                                    $status = '-';
                                }

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Tentukan tampilan status
                            |--------------------------------------------------------------------------
                            */

                            switch ($status) {

                                case 'hadir':

                                    $short = 'H';
                                    $class = 'status-solid-hadir';
                                    $presentCount++;

                                    break;


                                case 'terlambat':

                                    $short = 'T';
                                    $class = 'status-solid-terlambat';
                                    $lateCount++;

                                    break;


                                case 'izin':

                                    $short = 'I';
                                    $class = 'status-solid-izin';
                                    $excusedCount++;

                                    break;


                                case 'sakit':

                                    $short = 'S';
                                    $class = 'status-solid-sakit';
                                    $sickCount++;

                                    break;


                                case 'alpha':

                                    $short = 'A';
                                    $class = 'status-solid-alpha';
                                    $absentCount++;

                                    break;


                                default:

                                    $short = '-';
                                    $class = 'status-solid-none';

                                    break;

                            }

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | URL detail absensi
                        |--------------------------------------------------------------------------
                        */

                        $url = route(
                            'user.absen.history.detail',
                            ['date' => $dateStr]
                        );

                    @endphp


                    {{-- Jika tanggal berada di bulan yang dipilih --}}
                    @if ($isInMonth)

                        <a
                            href="{{ $url }}"
                            class="calendar-cell {{ $class }} {{ $isSunday ? 'calendar-sunday' : '' }}"
                        >

                            <div class="calendar-date">
                                {{ $current->format('d') }}
                            </div>

                            <div class="calendar-status">
                                {{ $short }}
                            </div>

                        </a>

                    @else

                        <div class="calendar-cell calendar-cell-empty"></div>

                    @endif


                    @php
                        $current->addDay();
                    @endphp

                @endfor

            @endwhile

        </div>

    </div>


    {{-- ========================= --}}
    {{-- LEGENDA --}}
    {{-- ========================= --}}

    <div class="mt-3">

        <div class="d-flex flex-wrap gap-2 small">

            <div class="d-flex align-items-center gap-1">
                <span class="legend-box status-solid-hadir"></span>
                Hadir
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="legend-box status-solid-terlambat"></span>
                Terlambat
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="legend-box status-solid-izin"></span>
                Izin
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="legend-box status-solid-sakit"></span>
                Sakit
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="legend-box status-solid-alpha"></span>
                Alpha
            </div>

        </div>

    </div>


    {{-- ========================= --}}
    {{-- STATISTIK --}}
    {{-- ========================= --}}

    <div class="row mt-4 g-2">

        {{-- Hadir --}}
        <div class="col-6 col-md-3">

            <div class="status-solid-hadir p-3 rounded h-100">

                <div class="fw-bold">
                    Hadir: {{ $presentCount + $lateCount }}
                </div>

                <small>
                    Terlambat: {{ $lateCount }}
                </small>

            </div>

        </div>


        {{-- Izin --}}
        <div class="col-6 col-md-3">

            <div class="status-solid-izin p-3 rounded h-100">

                <div class="fw-bold">
                    Izin: {{ $excusedCount }}
                </div>

            </div>

        </div>


        {{-- Sakit --}}
        <div class="col-6 col-md-3">

            <div class="status-solid-sakit p-3 rounded h-100">

                <div class="fw-bold">
                    Sakit: {{ $sickCount }}
                </div>

            </div>

        </div>


        {{-- Alpha --}}
        <div class="col-6 col-md-3">

            <div class="status-solid-alpha p-3 rounded h-100">

                <div class="fw-bold">
                    Absen: {{ $absentCount }}
                </div>

            </div>

        </div>

    </div>

</div>


{{-- ========================= --}}
{{-- CSS --}}
{{-- ========================= --}}

<style>

    /*
    |--------------------------------------------------------------------------
    | Container kalender
    |--------------------------------------------------------------------------
    */

    .calendar-wrapper {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }


    /*
    |--------------------------------------------------------------------------
    | Grid kalender
    |--------------------------------------------------------------------------
    */

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | Header hari
    |--------------------------------------------------------------------------
    */

    .calendar-head {
        text-align: center;
        font-weight: 600;
        padding: 10px 4px;
        font-size: 14px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }


    /*
    |--------------------------------------------------------------------------
    | Cell kalender
    |--------------------------------------------------------------------------
    */

    .calendar-cell {
        min-width: 0;
        min-height: 75px;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        text-decoration: none;

        border: 1px solid #dee2e6;

        padding: 6px;

        transition: 0.15s ease;

        color: inherit;
    }


    .calendar-cell:hover {
        transform: scale(1.02);
        position: relative;
        z-index: 2;
        text-decoration: none;
    }


    /*
    |--------------------------------------------------------------------------
    | Tanggal
    |--------------------------------------------------------------------------
    */

    .calendar-date {
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        margin-bottom: 5px;
    }


    /*
    |--------------------------------------------------------------------------
    | Status H/T/I/S/A
    |--------------------------------------------------------------------------
    */

    .calendar-status {
        font-size: 15px;
        font-weight: 700;
        line-height: 1;
    }


    /*
    |--------------------------------------------------------------------------
    | Minggu
    |--------------------------------------------------------------------------
    */

    .calendar-sunday {
        color: #dc3545 !important;
    }


    /*
    |--------------------------------------------------------------------------
    | Cell kosong
    |--------------------------------------------------------------------------
    */

    .calendar-cell-empty {
        background: #f8f9fa;
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .status-solid-hadir {
        background-color: #d1e7dd;
        color: #0f5132;
    }


    .status-solid-terlambat {
        background-color: #fff3cd;
        color: #664d03;
    }


    .status-solid-izin {
        background-color: #cff4fc;
        color: #055160;
    }


    .status-solid-sakit {
        background-color: #e2d9f3;
        color: #432874;
    }


    .status-solid-alpha {
        background-color: #f8d7da;
        color: #842029;
    }


    .status-solid-none {
        background-color: #ffffff;
        color: #6c757d;
    }


    /*
    |--------------------------------------------------------------------------
    | Legend
    |--------------------------------------------------------------------------
    */

    .legend-box {
        width: 14px;
        height: 14px;
        display: inline-block;
        border-radius: 3px;
    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 576px) {

        .calendar-head {
            font-size: 11px;
            padding: 8px 2px;
        }


        .calendar-cell {
            min-height: 58px;
            padding: 4px 2px;
        }


        .calendar-date {
            font-size: 11px;
            margin-bottom: 4px;
        }


        .calendar-status {
            font-size: 12px;
        }


        .calendar-cell:hover {
            transform: none;
        }


        .form-label {
            font-size: 14px;
        }


        .status-solid-hadir,
        .status-solid-terlambat,
        .status-solid-izin,
        .status-solid-sakit,
        .status-solid-alpha,
        .status-solid-none {
            border-radius: 6px;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | HP sangat kecil
    |--------------------------------------------------------------------------
    */

    @media (max-width: 375px) {

        .calendar-head {
            font-size: 10px;
        }


        .calendar-cell {
            min-height: 52px;
        }


        .calendar-date {
            font-size: 10px;
        }


        .calendar-status {
            font-size: 11px;
        }

    }

</style>

@endsection