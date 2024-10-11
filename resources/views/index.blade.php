<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>{{ config('app.name') }} - @yield('title', 'Home')</title>
</head>
<body>
<p>{{ auth()->user()->name }}</p>
<div id="content">
</div>
@vite(['resources/js/app.js'])
</body>

</html>
