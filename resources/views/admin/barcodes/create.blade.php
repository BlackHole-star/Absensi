@extends('layouts.app')

@section('title', 'Tambah QR Code')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #map {
        width: 100%;
        height: 350px;
        min-height: 300px;
    }

    .coordinate-input {
        font-family: monospace;
    }
</style>
@endpush

@section('content')

<div class="container my-4 px-3 px-md-5">

    <div class="surface-card">

        <h3 class="mb-2">
            Tambah QR Code Lokasi
        </h3>

        <a href="{{ route('admin.barcodes.index') }}"
           class="btn btn-sm btn-outline-secondary mb-4 d-inline-flex align-items-center gap-2">

            <i class="bi bi-arrow-left"></i>
            Kembali

        </a>

        {{-- Pesan error validasi --}}
        @if ($errors->any())

            <div class="alert alert-danger rounded-2">

                <strong>Terjadi kesalahan:</strong>

                <ul class="mb-0 mt-2">

                    @foreach ($errors->all() as $e)

                        <li>{{ $e }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        <form method="POST"
              action="{{ route('admin.barcodes.store') }}">

            @csrf

            <div class="row g-3">

                {{-- Nama Lokasi --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Nama Lokasi
                    </label>

                    <input type="text"
                           name="nama_lokasi"
                           class="form-control"
                           value="{{ old('nama_lokasi') }}"
                           placeholder="Contoh: Kantor Cabang Bogor"
                           required>

                </div>


                {{-- Kode QR --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Kode QR
                    </label>

                    <div class="input-group">

                        <input type="text"
                               name="kode_barcode"
                               id="barcodeValue"
                               class="form-control"
                               value="{{ old('kode_barcode') }}"
                               placeholder="Klik Generate"
                               required>

                        <button type="button"
                                onclick="generateBarcode()"
                                class="btn btn-outline-dark">

                            Generate

                        </button>

                    </div>

                </div>


                {{-- Latitude --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Latitude
                    </label>

                    <input type="number"
                           name="latitude"
                           id="latitude"
                           class="form-control coordinate-input"
                           value="{{ old('latitude') }}"
                           step="any"
                           min="-90"
                           max="90"
                           placeholder="Contoh: -6.200000"
                           required>

                    <small class="text-muted">
                        Nilai antara -90 sampai 90
                    </small>

                </div>


                {{-- Longitude --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Longitude
                    </label>

                    <input type="number"
                           name="longitude"
                           id="longitude"
                           class="form-control coordinate-input"
                           value="{{ old('longitude') }}"
                           step="any"
                           min="-180"
                           max="180"
                           placeholder="Contoh: 106.816666"
                           required>

                    <small class="text-muted">
                        Nilai antara -180 sampai 180
                    </small>

                </div>


                {{-- Radius --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Radius Valid Absen (meter)
                    </label>

                    <input type="number"
                           name="radius_meter"
                           id="radius_meter"
                           class="form-control"
                           value="{{ old('radius_meter', 50) }}"
                           min="1"
                           step="1"
                           required>

                    <small class="text-muted">
                        Contoh: 50 meter
                    </small>

                </div>


                {{-- IP Kantor --}}
                <div class="col-md-6">

                    <label class="form-label">
                        IP Kantor
                    </label>

                    <input type="text"
                           name="ip_kantor"
                           class="form-control"
                           value="{{ old('ip_kantor') }}"
                           placeholder="Contoh: 192.168.1.1">

                    <small class="text-muted">
                        Kosongkan jika tidak menggunakan validasi IP.
                    </small>

                </div>


                {{-- Status Aktif --}}
                <div class="col-md-6">

                    <div class="form-check mt-4">

                        <input class="form-check-input"
                               type="checkbox"
                               name="is_aktif"
                               id="is_aktif"
                               value="1"
                               {{ old('is_aktif', true) ? 'checked' : '' }}>

                        <label class="form-check-label"
                               for="is_aktif">

                            Aktif

                        </label>

                    </div>

                </div>

            </div>


            {{-- PETA --}}
            <div class="my-4">

                <button type="button"
                        onclick="toggleMap()"
                        class="btn btn-outline-secondary">

                    <i class="bi bi-map"></i>

                    Tampilkan / Sembunyikan Peta

                </button>


                <div id="map"
                     style="display: none;"
                     class="mt-3 rounded border shadow-sm">
                </div>

            </div>


            {{-- Tombol Simpan --}}
            <div class="text-end">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="bi bi-save"></i>

                    Simpan QR Code

                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

    let map = null;
    let marker = null;
    let circle = null;


    /*
    |--------------------------------------------------------------------------
    | Tampilkan / Sembunyikan Peta
    |--------------------------------------------------------------------------
    */

    function toggleMap() {

        const mapDiv = document.getElementById('map');

        const isHidden = mapDiv.style.display === 'none';

        mapDiv.style.display = isHidden ? 'block' : 'none';


        if (isHidden) {

            if (!map) {

                initMap();

            } else {

                setTimeout(function () {

                    map.invalidateSize();

                }, 200);

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Inisialisasi Peta
    |--------------------------------------------------------------------------
    */

    function initMap() {

        let lat = parseFloat(
            document.getElementById('latitude').value
        );

        let lng = parseFloat(
            document.getElementById('longitude').value
        );

        let radius = parseFloat(
            document.getElementById('radius_meter').value
        ) || 50;


        /*
        |--------------------------------------------------------------------------
        | Jika koordinat belum diisi
        |--------------------------------------------------------------------------
        */

        if (
            isNaN(lat) ||
            lat < -90 ||
            lat > 90
        ) {

            lat = -6.200000;

        }


        if (
            isNaN(lng) ||
            lng < -180 ||
            lng > 180
        ) {

            lng = 106.816666;

        }


        /*
        |--------------------------------------------------------------------------
        | Buat Map
        |--------------------------------------------------------------------------
        */

        map = L.map('map').setView(
            [lat, lng],
            16
        );


        /*
        |--------------------------------------------------------------------------
        | OpenStreetMap
        |--------------------------------------------------------------------------
        */

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution:
                    '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);


        /*
        |--------------------------------------------------------------------------
        | Marker
        |--------------------------------------------------------------------------
        */

        marker = L.marker(
            [lat, lng],
            {
                draggable: true
            }
        ).addTo(map);


        /*
        |--------------------------------------------------------------------------
        | Lingkaran Radius
        |--------------------------------------------------------------------------
        */

        drawCircle(
            lat,
            lng,
            radius
        );


        /*
        |--------------------------------------------------------------------------
        | Marker digeser
        |--------------------------------------------------------------------------
        */

        marker.on(
            'dragend',
            function (e) {

                const position =
                    e.target.getLatLng();

                updateCoordinates(
                    position.lat,
                    position.lng,
                    false
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Klik pada peta
        |--------------------------------------------------------------------------
        */

        map.on(
            'click',
            function (e) {

                updateCoordinates(
                    e.latlng.lat,
                    e.latlng.lng,
                    false
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Pastikan ukuran peta benar
        |--------------------------------------------------------------------------
        */

        setTimeout(function () {

            map.invalidateSize();

        }, 200);

    }


    /*
    |--------------------------------------------------------------------------
    | Update koordinat
    |--------------------------------------------------------------------------
    */

    function updateCoordinates(
        lat,
        lng,
        centerMap = true
    ) {

        lat = parseFloat(lat);
        lng = parseFloat(lng);


        if (
            isNaN(lat) ||
            isNaN(lng)
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Update input
        |--------------------------------------------------------------------------
        */

        document.getElementById('latitude').value =
            lat.toFixed(6);

        document.getElementById('longitude').value =
            lng.toFixed(6);


        /*
        |--------------------------------------------------------------------------
        | Update marker
        |--------------------------------------------------------------------------
        */

        if (marker) {

            marker.setLatLng(
                [lat, lng]
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Update map
        |--------------------------------------------------------------------------
        */

        if (
            map &&
            centerMap
        ) {

            map.setView(
                [lat, lng],
                map.getZoom()
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Update radius
        |--------------------------------------------------------------------------
        */

        const radius =
            parseFloat(
                document.getElementById('radius_meter').value
            ) || 50;


        drawCircle(
            lat,
            lng,
            radius
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Menggambar Radius
    |--------------------------------------------------------------------------
    */

    function drawCircle(
        lat,
        lng,
        radius
    ) {

        if (!map) {

            return;

        }


        if (circle) {

            circle.setLatLng(
                [lat, lng]
            );

            circle.setRadius(
                radius
            );

        } else {

            circle = L.circle(
                [lat, lng],
                {
                    radius: radius,
                    color: '#0d6efd',
                    fillColor: '#0d6efd',
                    fillOpacity: 0.2
                }
            ).addTo(map);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Ketika Latitude / Longitude diketik manual
    |--------------------------------------------------------------------------
    */

    function updateMapFromInput() {

        const lat =
            parseFloat(
                document.getElementById('latitude').value
            );

        const lng =
            parseFloat(
                document.getElementById('longitude').value
            );

        const radius =
            parseFloat(
                document.getElementById('radius_meter').value
            ) || 50;


        /*
        |--------------------------------------------------------------------------
        | Validasi sementara
        |--------------------------------------------------------------------------
        */

        if (
            isNaN(lat) ||
            isNaN(lng)
        ) {

            return;

        }


        if (
            lat < -90 ||
            lat > 90
        ) {

            return;

        }


        if (
            lng < -180 ||
            lng > 180
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Jika peta belum dibuka
        |--------------------------------------------------------------------------
        */

        if (!map) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Pindahkan marker
        |--------------------------------------------------------------------------
        */

        if (marker) {

            marker.setLatLng(
                [lat, lng]
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Pindahkan tampilan peta
        |--------------------------------------------------------------------------
        */

        map.setView(
            [lat, lng],
            map.getZoom()
        );


        /*
        |--------------------------------------------------------------------------
        | Update lingkaran radius
        |--------------------------------------------------------------------------
        */

        drawCircle(
            lat,
            lng,
            radius
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Mengambil lokasi perangkat
    |--------------------------------------------------------------------------
    */

    function getUserLocation() {

        if (
            !navigator.geolocation
        ) {

            alert(
                'Geolocation tidak didukung oleh browser ini.'
            );

            return;

        }


        navigator.geolocation.getCurrentPosition(

            function (position) {

                const lat =
                    position.coords.latitude;

                const lng =
                    position.coords.longitude;


                /*
                |--------------------------------------------------------------------------
                | Masukkan koordinat ke input
                |--------------------------------------------------------------------------
                */

                document.getElementById('latitude').value =
                    lat.toFixed(6);

                document.getElementById('longitude').value =
                    lng.toFixed(6);


                /*
                |--------------------------------------------------------------------------
                | Kalau peta sedang terbuka
                |--------------------------------------------------------------------------
                */

                if (map) {

                    updateCoordinates(
                        lat,
                        lng,
                        true
                    );

                }

            },

            function (error) {

                console.warn(
                    'Gagal mengambil lokasi:',
                    error.message
                );

            },

            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Kode QR
    |--------------------------------------------------------------------------
    */

    function generateBarcode() {

        const year =
            new Date().getFullYear();

        const random =
            Math.floor(
                1000 +
                Math.random() * 9000
            );

        const value =
            `LOC-${year}-${random}`;


        document.getElementById(
            'barcodeValue'
        ).value = value;

    }


    /*
    |--------------------------------------------------------------------------
    | Saat halaman selesai dimuat
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const latitudeInput =
                document.getElementById('latitude');

            const longitudeInput =
                document.getElementById('longitude');

            const radiusInput =
                document.getElementById('radius_meter');


            /*
            |--------------------------------------------------------------------------
            | Latitude diketik
            |--------------------------------------------------------------------------
            */

            latitudeInput.addEventListener(
                'input',
                function () {

                    updateMapFromInput();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Longitude diketik
            |--------------------------------------------------------------------------
            */

            longitudeInput.addEventListener(
                'input',
                function () {

                    updateMapFromInput();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Radius diubah
            |--------------------------------------------------------------------------
            */

            radiusInput.addEventListener(
                'input',
                function () {

                    const radius =
                        parseFloat(this.value);

                    const lat =
                        parseFloat(
                            latitudeInput.value
                        );

                    const lng =
                        parseFloat(
                            longitudeInput.value
                        );


                    if (
                        !isNaN(lat) &&
                        !isNaN(lng) &&
                        !isNaN(radius) &&
                        map
                    ) {

                        drawCircle(
                            lat,
                            lng,
                            radius
                        );

                    }

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Ambil lokasi otomatis ketika halaman dibuka
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'load',
        function () {

            getUserLocation();

        }
    );

</script>

@endpush