@extends('adminlte::page')

@section('title', 'Viewing ' . $team->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

@include('teams.profile')

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
@stop