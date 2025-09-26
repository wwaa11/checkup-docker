@extends("layouts.app")
@section("content")
    <h1>Station Index</h1>
    <ul>
        @foreach ($stations as $station)
            <li><a href="{{ route("stations.view", $station) }}">{{ $station->name }}</a></li>
        @endforeach
    </ul>
@endsection
@push("scripts")
@endpush
