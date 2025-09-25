@extends("layout.app")

@section("content")
    <div class="flex flex-col items-center justify-center">
        <h1 class="text-2xl font-bold">Login</h1>
        <form action="{{ route("login") }}" method="post">
            <div class="mt-4">
                <label class="block text-sm font-medium text-[#1b1b18]" for="userid">Userid</label>
                <input class="mt-1 block w-full border border-[#1b1b18] p-2" id="userid" type="text" name="userid" required>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-[#1b1b18]" for="password">Password</label>
                <input class="mt-1 block w-full border border-[#1b1b18] p-2" id="password" type="password" name="password" required>
            </div>
            <div class="mt-4">
                <button class="bg-[#141402] p-2 text-white" type="submit">Login123</button>
            </div>
        </form>
    </div>
@endsection
@push("scripts")
    <script>
        console.log("Login Page");
    </script>
@endpush
