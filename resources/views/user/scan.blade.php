@extends('layouts.app')

@section('title', 'Scan Absen')

@push('styles')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>

    /*
    |--------------------------------------------------------------------------
    | QR SCANNER CONTAINER
    |--------------------------------------------------------------------------
    */

    #qr-reader {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        background: #000;
        border-radius: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | VIDEO KAMERA
    |--------------------------------------------------------------------------
    */

    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        background: #000 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | AREA SCAN
    |--------------------------------------------------------------------------
    */

    #qr-reader__scan_region {
        width: 100% !important;
        height: 100% !important;
        position: relative;
        overflow: hidden;
    }

    #qr-reader__scan_region video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    #qr-reader__dashboard {
        position: absolute !important;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 20;
        background: rgba(0, 0, 0, 0.65);
        color: white;
    }

    #qr-reader__dashboard_section {
        padding: 8px;
    }

    #qr-reader__dashboard_section_csr {
        display: none !important;
    }

    /*
    |--------------------------------------------------------------------------
    | TOMBOL DASHBOARD
    |--------------------------------------------------------------------------
    */

    #qr-reader button {
        position: relative;
        z-index: 30;
    }

    /*
    |--------------------------------------------------------------------------
    | MAP
    |--------------------------------------------------------------------------
    */

    #map {
        min-height: 240px;
        border-radius: 8px;
        overflow: hidden;
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767px) {

        #qr-reader {
            min-height: 320px;
        }

        #qr-reader__scan_region {
            min-height: 320px;
        }

        #qr-reader video {
            min-height: 320px;
            object-fit: cover !important;
        }

    }

</style>

@endpush


@section('content')

<div class="container py-4">

    {{-- HEADER --}}

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="fw-semibold mb-0">
            Scan QR & Lokasi
        </h4>

    </div>


    {{-- CARD UTAMA --}}

    <div class="surface-card">

        {{-- INFORMASI WAKTU --}}

        <div class="row g-3 mb-3">

            <div class="col-md-6">

                <label class="form-label fw-semibold small mb-1">
                    Waktu Kerja
                </label>

                <input
                    type="text"
                    class="form-control form-control-sm"
                    value="09:00 - 17:00"
                    readonly
                >

            </div>


            <div class="col-md-6">

                <label class="form-label fw-semibold small mb-1">
                    Tanggal
                </label>

                <input
                    type="text"
                    class="form-control form-control-sm"
                    value="{{ now()->format('d/m/Y') }}"
                    readonly
                >

                <small class="text-muted mt-1 d-block">

                    Lokasi Anda:

                    <span id="currentLocation">
                        Mendeteksi...
                    </span>

                </small>

            </div>

        </div>


        {{-- QR + MAP --}}

        <div class="row g-3 mb-3">

            {{-- QR SCANNER --}}

            <div class="col-md-6">

                <label class="form-label fw-semibold small mb-1">
                    QR Scanner
                </label>

                <div
                    class="border rounded bg-dark"
                    style="
                        width: 100%;
                        aspect-ratio: 1 / 1;
                        overflow: hidden;
                    "
                >

                    <div
                        id="qr-reader"
                        style="
                            width: 100%;
                            height: 100%;
                        "
                    ></div>

                </div>

            </div>


            {{-- MAP --}}

            <div class="col-md-6">

                <label class="form-label fw-semibold small mb-1">
                    Peta Lokasi
                </label>

                <div
                    id="map"
                    class="border rounded"
                    style="
                        width: 100%;
                        height: 240px;
                    "
                ></div>


                {{-- STATUS ABSEN --}}

                <div
                    class="d-flex gap-2 mt-3 flex-column flex-sm-row"
                >

                    <div class="scan-status-box">

                        <div class="text-muted small mb-1">

                            <i
                                class="bi bi-box-arrow-in-right me-1"
                            ></i>

                            Absen Masuk

                        </div>

                        <div class="fw-semibold">

                            {{ $attendance?->jam_masuk ?? '-' }}

                        </div>

                    </div>


                    <div class="scan-status-box">

                        <div class="text-muted small mb-1">

                            <i
                                class="bi bi-box-arrow-right me-1"
                            ></i>

                            Absen Keluar

                        </div>

                        <div class="fw-semibold">

                            {{ $attendance?->jam_pulang ?? '-' }}

                        </div>

                    </div>


                    <div class="scan-status-box">

                        <div class="text-muted small mb-1">

                            <i
                                class="bi bi-geo-alt me-1"
                            ></i>

                            Koordinat

                        </div>

                        <div class="fw-semibold">

                            {{
                                $lastLocationLog
                                ? number_format(
                                    $lastLocationLog->latitude_absen,
                                    5
                                )
                                . ', ' .
                                number_format(
                                    $lastLocationLog->longitude_absen,
                                    5
                                )
                                : '-'
                            }}

                        </div>

                    </div>

                </div>


                {{-- BUTTON --}}

                <div
                    class="d-flex gap-2 mt-3 flex-column flex-sm-row"
                >

                    <a
                        href="{{ route('user.absen.izin') }}"
                        class="btn btn-warning w-100 scan-btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                    >

                        <i class="bi bi-file-earmark-text"></i>

                        Ajukan Izin

                    </a>


                    <a
                        href="{{ route('user.absen.history') }}"
                        class="btn btn-primary w-100 scan-btn-sm d-inline-flex align-items-center justify-content-center gap-2"
                    >

                        <i class="bi bi-clock-history"></i>

                        Riwayat Absen

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

{{-- HTML5 QR CODE --}}

<script
    src="https://unpkg.com/html5-qrcode"
    type="text/javascript"
></script>


{{-- LEAFLET --}}

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | VARIABLE
        |--------------------------------------------------------------------------
        */

        let lat = null;
        let lon = null;
        let acc = null;

        let map = null;
        let marker = null;

        let qrScanner = null;

        let sedangScan = false;


        /*
        |--------------------------------------------------------------------------
        | UPDATE LOCATION LABEL
        |--------------------------------------------------------------------------
        */

        function updateLocationLabel() {

            const locationElement =
                document.getElementById(
                    'currentLocation'
                );

            if (
                !locationElement
                || lat === null
                || lon === null
            ) {
                return;
            }

            const accText =
                acc !== null
                    ? ` (±${Math.round(acc)}m)`
                    : '';

            locationElement.textContent =
                `${lat.toFixed(5)}, ${lon.toFixed(5)}${accText}`;
        }


        /*
        |--------------------------------------------------------------------------
        | GPS
        |--------------------------------------------------------------------------
        */

        function initGPS() {

            console.log(
                'Memulai GPS...'
            );

            if (!navigator.geolocation) {

                showToast(
                    'Browser tidak mendukung GPS.',
                    'danger'
                );

                return;
            }


            navigator.geolocation.getCurrentPosition(

                function (position) {

                    lat =
                        position.coords.latitude;

                    lon =
                        position.coords.longitude;

                    acc =
                        position.coords.accuracy;


                    console.log(
                        'GPS BERHASIL:',
                        lat,
                        lon,
                        acc
                    );


                    updateLocationLabel();


                    /*
                    |--------------------------------------------------------------------------
                    | MAP
                    |--------------------------------------------------------------------------
                    */

                    map =
                        L.map('map')
                        .setView(
                            [lat, lon],
                            16
                        );


                    L.tileLayer(
                        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        {
                            maxZoom: 19,
                            attribution:
                                '&copy; OpenStreetMap'
                        }
                    ).addTo(map);


                    /*
                    |--------------------------------------------------------------------------
                    | MARKER
                    |--------------------------------------------------------------------------
                    */

                    const pulseIcon =
                        L.divIcon({
                            className: '',
                            html:
                                '<div class="scan-pulse-circle"></div>',
                            iconSize: [20, 20]
                        });


                    marker =
                        L.marker(
                            [lat, lon],
                            {
                                icon: pulseIcon
                            }
                        ).addTo(map);


                    /*
                    |--------------------------------------------------------------------------
                    | REAL TIME LOCATION
                    |--------------------------------------------------------------------------
                    */

                    navigator.geolocation.watchPosition(

                        function (pos) {

                            lat =
                                pos.coords.latitude;

                            lon =
                                pos.coords.longitude;

                            acc =
                                pos.coords.accuracy;


                            if (marker) {

                                marker.setLatLng(
                                    [lat, lon]
                                );

                            }


                            updateLocationLabel();

                        },

                        function (err) {

                            console.warn(
                                'Tracking lokasi gagal:',
                                err.message
                            );

                        },

                        {
                            enableHighAccuracy: true,
                            maximumAge: 0
                        }

                    );


                    /*
                    |--------------------------------------------------------------------------
                    | LOCATE BUTTON
                    |--------------------------------------------------------------------------
                    */

                    const locateBtn =
                        L.control({
                            position: 'topleft'
                        });


                    locateBtn.onAdd =
                        function () {

                            const btn =
                                L.DomUtil.create(
                                    'div',
                                    'leaflet-bar leaflet-control'
                                );


                            btn.style.backgroundColor =
                                '#fff';

                            btn.style.border =
                                'none';

                            btn.style.cursor =
                                'pointer';

                            btn.style.width =
                                '34px';

                            btn.style.height =
                                '34px';

                            btn.style.borderRadius =
                                '50%';

                            btn.style.display =
                                'flex';

                            btn.style.alignItems =
                                'center';

                            btn.style.justifyContent =
                                'center';


                            btn.innerHTML =
                                '<i class="bi bi-crosshair" style="color: var(--color-primary); font-size: 18px;"></i>';


                            btn.onclick =
                                function () {

                                    if (
                                        lat !== null
                                        &&
                                        lon !== null
                                    ) {

                                        map.flyTo(
                                            [lat, lon],
                                            16
                                        );

                                    }

                                };


                            return btn;

                        };


                    locateBtn.addTo(map);


                    setTimeout(
                        function () {

                            map.invalidateSize();

                        },
                        500
                    );

                },

                function (err) {

                    console.error(
                        'GPS ERROR:',
                        err
                    );


                    showToast(
                        'Gagal mengakses lokasi: ' +
                        err.message,
                        'danger'
                    );

                },

                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }

            );

        }


        /*
        |--------------------------------------------------------------------------
        | QR SCANNER
        |--------------------------------------------------------------------------
        */

        function initQRScanner() {

            console.log(
                'Memulai QR Scanner...'
            );


            if (
                typeof Html5Qrcode ===
                'undefined'
            ) {

                console.error(
                    'Html5Qrcode tidak tersedia.'
                );

                showToast(
                    'Library QR Scanner gagal dimuat.',
                    'danger'
                );

                return;
            }


            qrScanner =
                new Html5Qrcode(
                    'qr-reader'
                );


            const isMobile =
                /Android|iPhone|iPad|iPod/i.test(
                    navigator.userAgent
                );


            /*
            |--------------------------------------------------------------------------
            | KONFIGURASI KAMERA
            |--------------------------------------------------------------------------
            |
            | HP menggunakan kamera belakang.
            |
            */

            const cameraConfig =
                isMobile
                    ? {
                        facingMode: {
                            exact: 'environment'
                        }
                    }
                    : {
                        facingMode: 'user'
                    };


            console.log(
                'Konfigurasi kamera:',
                cameraConfig
            );


            qrScanner.start(

                cameraConfig,

                {

                    fps: 10,

                    qrbox:
                        function (w, h) {

                            const min =
                                Math.min(
                                    w,
                                    h
                                );

                            const size =
                                Math.floor(
                                    min * 0.60
                                );

                            return {
                                width: size,
                                height: size
                            };

                        },

                    aspectRatio: 1.0,

                    disableFlip:
                        isMobile

                },


                function (decodedText) {

                    console.log(
                        'QR TERBACA:',
                        decodedText
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | MENCEGAH SCAN BERULANG
                    |--------------------------------------------------------------------------
                    */

                    if (sedangScan) {

                        return;

                    }

                    sedangScan = true;


                    /*
                    |--------------------------------------------------------------------------
                    | CEK GPS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        lat === null
                        ||
                        lon === null
                        ||
                        acc === null
                    ) {

                        sedangScan = false;

                        showToast(
                            'Lokasi belum terdeteksi. Tunggu beberapa detik lalu coba scan ulang.',
                            'warning'
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STOP CAMERA
                    |--------------------------------------------------------------------------
                    */

                    qrScanner.stop()

                        .then(
                            function () {

                                console.log(
                                    'QR Scanner dihentikan.'
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | DATA ABSENSI
                                |--------------------------------------------------------------------------
                                */

                                const dataAbsensi =
                                    {
                                        barcode_value:
                                            decodedText,

                                        latitude:
                                            lat,

                                        longitude:
                                            lon,

                                        accuracy:
                                            acc
                                    };


                                console.log(
                                    'DATA ABSENSI:',
                                    dataAbsensi
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | KIRIM KE SERVER
                                |--------------------------------------------------------------------------
                                |
                                | Menggunakan URL relatif agar
                                | otomatis menggunakan HTTPS
                                | Cloudflare.
                                |
                                */

                                const url =
                                    '/user/absen/store';


                                console.log(
                                    'Mengirim request ke:',
                                    window.location.origin
                                    + url
                                );


                                fetch(
                                    url,
                                    {

                                        method:
                                            'POST',

                                        headers:
                                            {
                                                'X-CSRF-TOKEN':
                                                    document
                                                    .querySelector(
                                                        'meta[name="csrf-token"]'
                                                    )
                                                    ?.getAttribute(
                                                        'content'
                                                    )
                                                    ||
                                                    "{{ csrf_token() }}",

                                                'Content-Type':
                                                    'application/json',

                                                'Accept':
                                                    'application/json'
                                            },

                                        credentials:
                                            'same-origin',

                                        body:
                                            JSON.stringify(
                                                dataAbsensi
                                            )

                                    }
                                )

                                .then(
                                    async function (response) {

                                        console.log(
                                            'HTTP STATUS:',
                                            response.status
                                        );


                                        const text =
                                            await response.text();


                                        console.log(
                                            'RAW RESPONSE:',
                                            text
                                        );


                                        let data;


                                        try {

                                            data =
                                                JSON.parse(
                                                    text
                                                );

                                        } catch (e) {

                                            throw new Error(
                                                'Server mengembalikan response yang bukan JSON.'
                                            );

                                        }


                                        return data;

                                    }
                                )

                                .then(
                                    function (data) {

                                        console.log(
                                            'RESPON SERVER:',
                                            data
                                        );


                                        showToast(

                                            data.message
                                                ||
                                                'Absen berhasil',

                                            data.success
                                                ? 'success'
                                                : 'danger'

                                        );


                                        setTimeout(
                                            function () {

                                                location.reload();

                                            },

                                            data.success
                                                ? 1200
                                                : 2500
                                        );

                                    }
                                )

                                .catch(
                                    function (error) {

                                        console.error(
                                            'FETCH ERROR:',
                                            error
                                        );


                                        sedangScan =
                                            false;


                                        showToast(
                                            'Gagal mengirim absen: ' +
                                            error.message,
                                            'danger'
                                        );

                                    }
                                );

                            }
                        )

                        .catch(
                            function (error) {

                                console.error(
                                    'QR STOP ERROR:',
                                    error
                                );

                                sedangScan =
                                    false;

                            }
                        );

                },


                function (errorMessage) {

                    /*
                    |--------------------------------------------------------------------------
                    | ERROR SCAN DIAMKAN
                    |--------------------------------------------------------------------------
                    |
                    | html5-qrcode akan memanggil callback
                    | ini berkali-kali ketika QR belum ditemukan.
                    |
                    */

                }

            )

            .then(
                function () {

                    console.log(
                        'QR SCANNER BERHASIL DIMULAI.'
                    );

                }
            )

            .catch(
                function (error) {

                    console.error(
                        'QR SCANNER ERROR:',
                        error
                    );


                    showToast(
                        'Tidak bisa mengakses kamera: ' +
                        error,
                        'danger'
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | START
        |--------------------------------------------------------------------------
        */

        initGPS();

        initQRScanner();

    }

);

</script>

@endpush