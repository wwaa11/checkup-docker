@extends("layouts.patient")

@section("content")
    @php
        if ($prefer_thai) {
            $text = [
                "location-permission" => "โปรดอนุญาตการเข้าถึงตำแหน่ง",
                "location-check" => "กำลังตรวจสอบตำแหน่ง",
                "location-false" => "ไม่อยู่ในระยะที่สำมารถทำรายการได้",
                "name" => "ชื่อ - นามสกุล",
                "checkin" => "Check-in",
                "wait-number" => "ระบบกำลังตรวจสอบคิว",
            ];
        } else {
            $text = [
                "location-permission" => "Please allow location access.",
                "location-check" => "Checking location...",
                "location-false" => "You are not in the distance that the bus can operate.",
                "name" => "First Name - Last Name",
                "checkin" => "Check-in",
                "wait-number" => "System is checking queue number.",
            ];
        }
    @endphp
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <span>{{ $text["name"] }}</span>
                        <h4 class="text-center">
                            {{ $patient["name"] }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <span>HN </span>
                        <p class="text-center">
                            {{ $patient["hn"] }}
                        </p>
                    </div>
                    <div>
                        appointment time: {{ date("H:00", strtotime($appointment->AppointDateTime)) }}
                    </div>
                    <div id="check-section">
                        <p>
                            {{ $text["location-check"] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push("scripts")
    <script>
        $(document).ready(function() {
            navigator.geolocation.getCurrentPosition(success, error, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0,
            })

            setTimeout(function() {
                checkLocation()
            }, 1 * 500);
        });

        var lat = '-';
        var log = '-';

        function success(pos) {
            const crd = pos.coords;
            lat = crd.latitude;
            log = crd.longitude;
        }

        function error(err) {
            Swal.fire({
                title: "{{ $text["location-permission"] }}",
                icon: "error",
                allowOutsideClick: false,
                showConfirmButton: false,
                showCancelButton: false,
            });
        }

        async function checkLocation() {
            if (lat == '-' || log == '-') {
                setTimeout(function() {
                    checkLocation()
                }, 1 * 1000);
            } else {
                const formData = new FormData();
                formData.append('lat', lat);
                formData.append('log', log);
                const res = await axios.post("{{ route("patient.sms.check", $patient["hn"]) }}", formData).then((res) => {
                    let html = '';
                    if (!res.data.distant) {
                        html = `
                            <div>
                                {{ $text["location-false"] }}
                            </div>
                        `;
                    }
                    if (res.data.distant && res.data.canCheck) {
                        html = `
                            <div onclick="checkIn()">
                                {{ $text["checkin"] }}
                            </div>
                        `;
                    }
                    if (res.data.distant && !res.data.canCheck) {
                        html = `
                            <div>
                                CHECK-IN : ${res.data.master.checkinTime}
                            </div>
                        `;
                        if (res.data.master.number != null) {
                            html += `
                                <div>
                                    ${res.data.master.number}
                                </div>
                            `;
                        } else {
                            html += `
                                <div>
                                    {{ $text["wait-number"] }}
                                </div>
                            `;
                            checkNumber();
                        }
                    }
                    $("#check-section").html(html);
                })
            }
        }

        async function checkIn() {
            const res = await axios.post("{{ route("patient.sms.check-in", $patient["hn"]) }}").then((res) => {
                if (res.data.success) {
                    swal = Swal.fire({
                        title: "Check-in success!",
                        icon: "success",
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                        timer: 1000,
                    });
                    swal.then((result) => {
                        if (result.dismiss === Swal.DismissReason.timer) {
                            window.location.reload();
                        }
                    })
                }
            })
        }

        async function checkNumber() {
            const res = await axios.post("{{ route("patient.sms.check-number", $patient["hn"]) }}").then((res) => {
                if (res.data.success) {
                    location.reload();
                } else {
                    setTimeout(function() {
                        checkNumber()
                    }, 1000);
                }
            })
        }
    </script>
@endpush
