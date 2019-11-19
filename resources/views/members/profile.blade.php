@extends('adminlte::page')

@section('title', 'Viewing Profile - ' . $user->first_name . ' ' . $user->last_name)

@section('content_header')
    <h1>Member Directory</h1>
@stop

@section('content')
@include('shared.alerts')

@include('users.profile')

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
