@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container my-4 px-3 px-md-5">
    <div class="surface-card">

        @php
            $tanggalAwal = $dateRange[0] ?? null;
            $tanggalAkhir = !empty($dateRange) ? end($dateRange) : null;
        @endphp

        <h3 class="mb-4 fw-semibold text-dark">
            Rekap Absensi
            @if($type === 'monthly' && $month)
                Bulanan — {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
            @elseif($type === 'weekly' && $tanggalAwal && $tanggalAkhir)
                Mingguan — {{ \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d M Y') }}
            @elseif($type === 'daily' && $date)
                Harian — {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            @elseif($type === 'yearly' && $year)
                Tahunan — {{ $year }}
            @else
                Harian — {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}
            @endif
        </h3>

        {{-- Form filter rekap --}}
        <form method="GET" class="row row-cols-1 row-cols-md-auto g-3 align-items-end mb-4">
            <div class="col">
                <label for="year" class="form-label">Tahun</label>
                <input
                    type="number"
                    id="year"
                    name="year"
                    class="form-control"
                    min="2021"
                    max="{{ now()->year }}"
                    placeholder="Contoh: 2025"
                    value="{{ $year ?? '' }}">
            </div>

            <div class="col">
                <label for="month" class="form-label">Bulan</label>
                <input
                    type="month"
                    id="month"
                    name="month"
                    class="form-control"
                    max="{{ now()->format('Y-m') }}"
                    value="{{ $month ?? '' }}">
            </div>

            <div class="col">
                <label for="week" class="form-label">Minggu</label>
                <input
                    type="week"
                    id="week"
                    name="week"
                    class="form-control"
                    max="{{ now()->format('o-\WW') }}"
                    value="{{ $week ?? '' }}">
            </div>

            <div class="col">
                <label for="date" class="form-label">Tanggal</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="form-control"
                    max="{{ now()->format('Y-m-d') }}"
                    value="{{ $date ?? '' }}">
            </div>

            <div class="col">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-funnel"></i> Tampilkan
                </button>
            </div>
        </form>

        {{-- Tabel rekap absensi --}}
        <div class="table-responsive table-scroll-shadow">
            <table class="table table-bordered table-sm align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th class="rekap-sticky-col">Nama Karyawan</th>

                        @if ($type !== 'yearly')
                            @foreach ($dateRange as $tanggal)
                                <th>{{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}</th>
                            @endforeach
                        @endif

                        <th>H</th>
                        <th>T</th>
                        <th>I</th>
                        <th>S</th>
                        <th>A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapData as $data)
                        <tr>
                            <td class="text-start rekap-sticky-col">{{ $data['nama'] }}</td>

                            @if ($type !== 'yearly')
                                @foreach ($data['status_harian'] as $status)
                                    @php
                                        $warnaLatar = match($status) {
                                            'H' => 'rekap-cell-hadir',
                                            'T' => 'rekap-cell-terlambat',
                                            'I' => 'rekap-cell-izin',
                                            'S' => 'rekap-cell-sakit',
                                            'A' => 'rekap-cell-alpha',
                                            default => '',
                                        };
                                    @endphp

                                    <td class="{{ $warnaLatar }}">{{ $status }}</td>
                                @endforeach
                            @endif

                            <td>{{ $data['ringkasan']['H'] }}</td>
                            <td>{{ $data['ringkasan']['T'] }}</td>
                            <td>{{ $data['ringkasan']['I'] }}</td>
                            <td>{{ $data['ringkasan']['S'] }}</td>
                            <td>{{ $data['ringkasan']['A'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($dateRange) + 6 }}" class="p-0">
                                @include('components.empty-state', ['message' => 'Tidak ada data rekap absensi yang tersedia.'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Keterangan kode status --}}
        <div class="mt-3 small text-muted">
            <strong>Keterangan:</strong>
            H = Hadir,
            T = Terlambat,
            I = Izin,
            S = Sakit,
            A = Alpha,
            - = Hari Libur
        </div>
    </div>
</div>

<script>
    const sekarang = new Date();

    const inputTahun = document.getElementById('year');
    const inputBulan = document.getElementById('month');
    const inputMinggu = document.getElementById('week');
    const inputTanggal = document.getElementById('date');

    /**
     * Menambahkan angka 0 di depan angka tunggal.
     * Contoh: 7 menjadi 07.
     */
    function pad(angka) {
        return angka < 10 ? '0' + angka : angka;
    }

    /**
     * Mengubah input minggu ISO menjadi tanggal awal minggu.
     *
     * Contoh:
     * minggu ke-10 tahun 2025 akan dikonversi
     * menjadi tanggal awal minggu tersebut.
     */
    function getDateOfISOWeek(minggu, tahun) {
        const tanggalSederhana = new Date(tahun, 0, 1 + (minggu - 1) * 7);
        const hariKe = tanggalSederhana.getDay();
        const awalMingguISO = new Date(tanggalSederhana);

        if (hariKe <= 4) {
            awalMingguISO.setDate(tanggalSederhana.getDate() - tanggalSederhana.getDay() + 1);
        } else {
            awalMingguISO.setDate(tanggalSederhana.getDate() + 8 - tanggalSederhana.getDay());
        }

        return awalMingguISO;
    }

    /**
     * Memvalidasi agar filter tahun, bulan, minggu, dan tanggal
     * tidak saling bertentangan dan tidak melebihi hari ini.
     *
     * Contoh validasi:
     * - tanggal tidak boleh di masa depan
     * - bulan harus sesuai dengan tahun jika tahun dipilih
     * - minggu harus sesuai dengan tahun/bulan jika dipilih
     * - tanggal harus berada di minggu yang sama jika filter minggu dipakai
     */
    function validasiBatasFilter() {
        const tahun = parseInt(inputTahun.value);
        const bulan = inputBulan.value;
        const minggu = inputMinggu.value;
        const tanggal = inputTanggal.value;
        const tanggalDipilih = tanggal ? new Date(tanggal) : null;

        if (tanggalDipilih && tanggalDipilih > sekarang) {
            inputTanggal.value = '';
        }

        if (tahun) {
            if (tahun > sekarang.getFullYear()) {
                inputTahun.value = '';
            }

            if (bulan) {
                const [tahunBulan] = bulan.split('-');
                if (parseInt(tahunBulan) !== tahun || new Date(bulan + '-01') > sekarang) {
                    inputBulan.value = '';
                }
            }

            if (minggu) {
                const [tahunMinggu] = minggu.split('-W');
                const tanggalMinggu = getDateOfISOWeek(parseInt(minggu.split('-W')[1]), parseInt(tahunMinggu));

                if (parseInt(tahunMinggu) !== tahun || tanggalMinggu > sekarang) {
                    inputMinggu.value = '';
                }
            }

            if (tanggalDipilih && tanggalDipilih.getFullYear() !== tahun) {
                inputTanggal.value = '';
            }
        }

        if (bulan) {
            const [tahunBulan, nomorBulan] = bulan.split('-');
            const tanggalBulan = new Date(bulan + '-01');

            if (tanggalBulan > sekarang) {
                inputBulan.value = '';
            }

            if (minggu) {
                const [tahunMinggu, nomorMinggu] = minggu.split('-W');
                const awalMinggu = getDateOfISOWeek(parseInt(nomorMinggu), parseInt(tahunMinggu));

                if (
                    awalMinggu.getMonth() + 1 !== parseInt(nomorBulan) ||
                    tahunMinggu !== tahunBulan ||
                    awalMinggu > sekarang
                ) {
                    inputMinggu.value = '';
                }
            }

            if (tanggalDipilih) {
                if (
                    tanggalDipilih.getFullYear() !== parseInt(tahunBulan) ||
                    tanggalDipilih.getMonth() + 1 !== parseInt(nomorBulan)
                ) {
                    inputTanggal.value = '';
                }
            }
        }

        if (minggu && tanggalDipilih) {
            const [tahunMinggu, nomorMinggu] = minggu.split('-W');
            const awalMinggu = getDateOfISOWeek(parseInt(nomorMinggu), parseInt(tahunMinggu));
            const akhirMinggu = new Date(awalMinggu);

            akhirMinggu.setDate(akhirMinggu.getDate() + 6);

            if (
                tanggalDipilih < awalMinggu ||
                tanggalDipilih > akhirMinggu ||
                tanggalDipilih > sekarang
            ) {
                inputTanggal.value = '';
            }
        }
    }

    /**
     * Menjalankan validasi setiap kali salah satu input filter berubah.
     */
    [inputTahun, inputBulan, inputMinggu, inputTanggal].forEach(input => {
        input?.addEventListener('change', validasiBatasFilter);
    });
</script>
@endsection