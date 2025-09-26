@extends("layouts.app")

@section("content")
    <div class="flex gap-3">
        <button class="bg-green-600 p-3" onclick="dispatchGenerateNumber()">Dispatch Generate Number</button>
    </div>
    <div class="">
        <h1>Queue Monitoring</h1>

        <div class="">
            <h3>Current Jobs</h3>
            <div class="flex">
                <div class="flex-1">
                    <p>ID: {{ $currentJobs["id"] }}</p>
                </div>
                <div class="flex-1">
                    <p>Job: {{ $currentJobs["job"] }}</p>
                </div>
                <div class="flex-1">
                    <p>Attempts: {{ $currentJobs["attempts"] }}</p>
                </div>
                <div class="flex-1">
                    <p>Created At: {{ $currentJobs["created_at"] }}</p>
                </div>
            </div>
        </div>

        <div class="">
            <h3>Delayed Jobs</h3>
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job</th>
                        <th>Execute At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delayedJobs as $job)
                        <tr>
                            <td>{{ $job["id"] }}</td>
                            <td>{{ $job["job"] }}</td>
                            <td>{{ $job["execute_at"] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push("scripts")
    <script>
        function dispatchGenerateNumber() {
            axios.post('{{ route("services.dispatch.generate-number") }}')
                .then(function(response) {
                    console.log('Generate number dispatched:', response.data);
                })
                .catch(function(error) {
                    console.error('Error dispatching generate number:', error);
                });
        };
    </script>
@endpush
