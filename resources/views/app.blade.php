<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php($adminFavicon = \App\Models\SiteSetting::current()['favicon'] ?? asset('farbo/assets/img/logo/logo.jpeg'))
@if($adminFavicon)
<link rel="icon" href="{{ $adminFavicon }}">
@endif
<script>window.farboCompanyName = @json(\App\Models\SiteSetting::current()['company_name'] ?? 'FARBO');</script>
@vite('resources/js/app.js')
@inertiaHead
</head>
<body>@inertia</body>
</html>

