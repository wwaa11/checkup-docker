@extends("layouts.patient")

@section("content")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    @if ($patient)
                        @php
                            if ($prefer_thai) {
                                $text = [
                                    "title" => "ไม่พบการนัดหมายวันนี้",
                                    "message" => "กรุณาติดต่อเจ้าหน้าที่",
                                ];
                            } else {
                                $text = [
                                    "title" => "Appointment not found",
                                    "message" => "Please contact the staff",
                                ];
                            }
                        @endphp
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ $text["title"] }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-center">
                                {{ $text["message"] }}
                            </p>
                        </div>
                    @else
                        <div>
                            ไม่พบข้อมูลผู้ป่วย.
                        </div>
                        <p>กรุณาติดต่อเจ้าหน้าที่. หรือตรวจสอบ URL อีกครั้ง</p>
                        <div>
                            Patient info notfound.
                        </div>
                        <p>please contact the staff. or verify url again.</p>
                        @if ($message)
                            <p>{{ $message }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
