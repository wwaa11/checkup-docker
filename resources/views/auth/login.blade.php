@extends("layouts.app")

@section("content")
    <div class="flex flex-col items-center justify-center">
        <h1 class="text-2xl font-bold">Login</h1>
        <div class="mt-4">
            <label class="block text-sm font-medium text-[#1b1b18]" for="userid">Userid</label>
            <input class="mt-1 block w-full border border-[#1b1b18] p-2" id="userid" type="text" name="userid" value="{{ env("APP_ENV") == "local" ? "650017" : null }}" required>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-[#1b1b18]" for="password">Password</label>
            <input class="mt-1 block w-full border border-[#1b1b18] p-2" id="password" type="password" name="password" value="{{ env("APP_ENV") == "local" ? "dev" : null }}" required>
        </div>
        <div class="mt-4">
            <button class="bg-[#141402] p-2 text-white" type="submit" onclick="login()">Login</button>
        </div>
    </div>
@endsection
@push("scripts")
    <script>
        async function login() {
            const userid = $("#userid").val();
            const password = $("#password").val();
            const response = await axios.post("{{ route("login.post") }}", {
                userid,
                password
            });
            if (response.data.status === "success") {
                window.location.href = response.data.stationRedirect;
            } else {
                Swal.fire({
                    title: "Error",
                    text: response.data.message,
                    icon: "error"
                });
            }
        }
    </script>
@endpush
