@extends('adminlte::page')

@section('title', 'Viewing ' . $gatekeeper->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

@include('gatekeeper.profile')

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
@stop
