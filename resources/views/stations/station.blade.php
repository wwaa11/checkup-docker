@extends("layouts.app")
@section("content")
    <select id="station-select" name="station" onchange="chageStation()">
        @foreach ($allStations as $selectStation)
            <option value="{{ route("stations.view", $selectStation->code) }}" @if ($station->code == $selectStation->code) selected @endif>{{ $selectStation->name }}</option>
        @endforeach
    </select>
    <h1>{{ $station->name }}</h1>
    <div>
        <a href="{{ route("stations.register", $station->code) }}">
            Register {{ $station->name }}
        </a>
    </div>
    @foreach ($station->rooms as $room)
        <p>{{ $room->name }}</p>
    @endforeach
@endsection
@push("scripts")
    <script>
        function chageStation() {
            const stationHref = $('#station-select').val();
            window.location.href = stationHref;
            console.log(stationHref)
        }
    </script>
@endpush
