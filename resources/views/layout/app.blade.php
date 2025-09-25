<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config("app.name", "Laravel") }}</title>
    <script src="{{ asset("js/jquery.js") }}"></script>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>

<body>
    <div class="min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        @auth
            <div class="flex">
                <div>Welcome, {{ Auth::user()->name }}</div>
                <a class="ml-auto bg-[#141402] p-2 text-white" href="{{ route("logout") }}">Logout</a>
            </div>
        @endauth
        @yield("content")
    </div>
</body>
@stack("scripts")

</html>
