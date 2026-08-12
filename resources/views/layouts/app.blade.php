<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

   
    <script src="{{ Storage::url('js/jquery-3.6.0.min.js') }}"></script>
    <link href="{{ Storage::url('css/select2.min.css') }}" rel="stylesheet"/>
    <script src="{{ Storage::url('js/select2.min.js') }}" defer></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @stack('header')

    <!-- Styles -->
    @yield('custom_header')

    @livewireStyles
</head>
<body class="font-sans antialiased">
<x-banner class="print:hidden"/>

<div class="min-h-screen bg-gray-100 ">
    @livewire('navigation-menu')

    <!-- Page Heading -->
    @if (isset($header))
        <header class="bg-white shadow print:hidden">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>

        </header>

    @endif

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
</div>

@stack('modals')

@livewireScripts
@yield('custom_script')
<script>
    $('form').submit(function () {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>
</body>
</html>
