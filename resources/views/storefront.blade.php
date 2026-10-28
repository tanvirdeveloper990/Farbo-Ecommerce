@extends('farbo.layout')
@section('inertia', 'true')
@push('head')
@vite('resources/js/storefront.js')
@inertiaHead
@endpush
@section('content')
@inertia
@endsection
