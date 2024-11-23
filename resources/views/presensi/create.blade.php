@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-Presensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
    <style>
        .webcam-capture,
        .webcam-capture video {
            display: inline-block;
            width: 100% !important;
            margin: auto;
            margin-top: 20px;
            height: auto !important;
            border-radius: 15px;
        }

        #map {
            height: 200px;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
@section('content')
    <div class="row mt-0">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="lokasi">
                    <div class="webcam-capture text-center"></div>
                    @if ($cek > 0)
                        <button id="takeabsen" class="btn btn-danger btn-block"><ion-icon name="camera-outline"></ion-icon>
                            Absen Pulang
                        </button>
                    @else
                        <button id="takeabsen" class="btn btn-primary btn-block"><ion-icon name="camera-outline"></ion-icon>
                            Absen Masuk
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
            <audio id="notifikasi_in">
                <source src="{{ asset('assets/sound/notifikasi_in.mp3') }}" type= "audio/mpeg">
                <audio id="notifikasi_out">
                    <source src="{{ asset('assets/sound/notifikasi_out.mp3') }}" type= "audio/mpeg">
                </audio>
                <audio id="radius_sound">
                    <source src="{{ asset('assets/sound/radius.mp3') }}" type= "audio/mpeg">
                </audio>
            @endsection

            @push('myscript')
                <script>
                    var notifikasi_in = document.getElementById('notifikasi_in');
                    var notifikasi_out = document.getElementById('notifikasi_out');
                    var radius_sound = document.getElementById('radius_sound');
                    Webcam.set({
                        height: 480,
                        width: 600,
                        image_format: 'jpeg',
                        jpeg_quality: 80
                    });

                    Webcam.attach('.webcam-capture');

                    var lokasi = document.getElementById('lokasi');
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
                            enableHighAccuracy: true,
                            timeout: 10000
                        });
                    }

                    function successCallback(position) {
                        var latitude = position.coords.latitude;
                        var longitude = position.coords.longitude;
                        lokasi.value = latitude + "," + longitude;
                        var map = L.map('map').setView([latitude, longitude], 12);

                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(map);
                        var marker = L.marker([latitude, longitude]).addTo(map);
                        var circle = L.circle([-6.315881738075358, 106.76022049158006], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.5,
                            radius: 10
                        }).addTo(map);
                    }

                    function errorCallback() {
                        alert('Unable to retrieve your location.');
                    }

                    var image; // Deklarasi variabel image di luar fungsi

                    $("#takeabsen").click(function(e) {
                        Webcam.snap(function(uri) {
                            image = uri;
                        });
                        var lokasi = $("#lokasi").val();
                        $.ajax({
                            type: 'POST',
                            url: '/presensi/store',
                            data: {
                                _token: "{{ csrf_token() }}",
                                image: image,
                                lokasi: lokasi
                            },
                            cache: false,
                            success: function(respond) {
                                var status = respond.split("|");
                                if (status[0] == "success") {
                                    if (status[2] == "in") {
                                        notifikasi_in.play();
                                    } else {
                                        notifikasi_out.play();
                                    }
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: status[1],
                                        icon: 'success',
                                    })
                                    setTimeout("location.href= '/dashboard'", 3000);
                                } else {
                                    if (status[2] == "radius") {
                                        radius_sound.play();
                                    }
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: status[1],
                                        icon: 'error',
                                    })
                                }
                            }
                        });
                    });
                </script>
            @endpush
