@extends('layouts.app')

@section('title', 'Edit QR Code')

@push('styles')

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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

        <h3 class="mb-4">
            Edit QR Code Lokasi
        </h3>


        <a href="{{ route('admin.barcodes.index') }}"
           class="btn btn-outline-secondary mb-3">

            <i class="bi bi-arrow-left"></i>
            Kembali

        </a>


        {{-- Error Validasi --}}
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
              action="{{ route('admin.barcodes.update', $barcode->id) }}">

            @csrf

            @method('PUT')


            {{-- Baris pertama --}}
            <div class="row g-3 align-items-end">

                {{-- Nama Lokasi --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Nama Lokasi
                    </label>

                    <input type="text"
                           name="nama_lokasi"
                           class="form-control"
                           value="{{ old('nama_lokasi', $barcode->nama_lokasi) }}"
                           required>

                </div>


                {{-- Kode QR --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Kode QR
                    </label>

                    <input type="text"
                           name="kode_barcode"
                           class="form-control"
                           value="{{ old('kode_barcode', $barcode->kode_barcode) }}"
                           required>

                </div>

            </div>


            {{-- Koordinat --}}
            <div class="row g-3 mt-1">

                {{-- Latitude --}}
                <div class="col-md-6">

                    <label class="form-label">
                        Latitude
                    </label>

                    <input type="number"
                           name="latitude"
                           id="latitude"
                           class="form-control coordinate-input"
                           value="{{ old('latitude', $barcode->latitude) }}"
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
                           value="{{ old('longitude', $barcode->longitude) }}"
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
                           value="{{ old('radius_meter', $barcode->radius_meter) }}"
                           min="1"
                           step="1"
                           required>

                </div>


                {{-- IP Kantor --}}
                <div class="col-md-6">

                    <label class="form-label">
                        IP Kantor
                    </label>

                    <input type="text"
                           name="ip_kantor"
                           class="form-control"
                           value="{{ old('ip_kantor', $barcode->ip_kantor) }}"
                           placeholder="Contoh: 192.168.1.1">

                    <small class="text-muted">
                        Kosongkan jika tidak menggunakan validasi IP.
                    </small>

                </div>


                {{-- Status --}}
                <div class="col-md-6">

                    <div class="form-check mt-4">

                        <input class="form-check-input"
                               type="checkbox"
                               name="is_aktif"
                               id="is_aktif"
                               value="1"
                               {{ old('is_aktif', $barcode->is_aktif) ? 'checked' : '' }}>

                        <label class="form-check-label"
                               for="is_aktif">

                            Aktif

                        </label>

                    </div>

                </div>

            </div>


            {{-- Peta --}}
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


            {{-- Tombol Update --}}
            <div class="text-end">

                <button type="submit"
                        class="btn btn-success">

                    <i class="bi bi-save"></i>

                    Update QR Code

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

        const mapDiv =
            document.getElementById('map');

        const isHidden =
            mapDiv.style.display === 'none';


        mapDiv.style.display =
            isHidden ? 'block' : 'none';


        if (isHidden) {

            if (!map) {

                initMap();

            } else {

                setTimeout(
                    function () {

                        map.invalidateSize();

                    },
                    200
                );

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Inisialisasi Peta
    |--------------------------------------------------------------------------
    */

    function initMap() {

        const lat =
            parseFloat(
                document.getElementById('latitude').value
            ) || -6.200000;


        const lng =
            parseFloat(
                document.getElementById('longitude').value
            ) || 106.816666;


        const radius =
            parseFloat(
                document.getElementById('radius_meter').value
            ) || 50;


        /*
        |--------------------------------------------------------------------------
        | Buat peta
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
        | Radius
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
        | Klik peta
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
        | Refresh ukuran map
        |--------------------------------------------------------------------------
        */

        setTimeout(
            function () {

                map.invalidateSize();

            },
            200
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Update Koordinat
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

        document.getElementById(
            'latitude'
        ).value = lat.toFixed(6);


        document.getElementById(
            'longitude'
        ).value = lng.toFixed(6);


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
    | Update Peta berdasarkan Input
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
        | Koordinat belum lengkap
        |--------------------------------------------------------------------------
        */

        if (
            isNaN(lat) ||
            isNaN(lng)
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Validasi Latitude
        |--------------------------------------------------------------------------
        */

        if (
            lat < -90 ||
            lat > 90
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Validasi Longitude
        |--------------------------------------------------------------------------
        */

        if (
            lng < -180 ||
            lng > 180
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Peta belum dibuka
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
        | Pindahkan peta
        |--------------------------------------------------------------------------
        */

        map.setView(
            [lat, lng],
            map.getZoom()
        );


        /*
        |--------------------------------------------------------------------------
        | Update radius
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
    | Gambar Lingkaran Radius
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
    | Saat halaman dimuat
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
            | Latitude berubah
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
            | Longitude berubah
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
            | Radius berubah
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

</script>

@endpush