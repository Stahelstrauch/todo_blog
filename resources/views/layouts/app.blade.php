<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'TODO ja ajaveeb')</title>
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
    {{-- Menüü --}}
    <div class="container">
        @include('layouts.nav')
    </div>
    {{-- Põhi sisu --}}
    <div class="container">
        @yield('content')
    </div>

    {{-- Jalus --}}
    <footer class="">
        <div class="container">
            <div class="row">
                <div class="mb-3 text-center">
                    &copy; {{ date('Y') }} To-Do ja Blogi (v2)
                    <p class="text-muted"></p>
                </div>
                                
            </div>
            
        </div>
    </footer>

    {{-- Vajalik @push('scripts') jaoks --}}
    @stack('scripts') 
</body>
</html>