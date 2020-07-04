@extends('adminlte::page')

@section('title', 'Viewing Form Submission')

@section('content_header')
    <h1>Viewing Form Submission</h1>
@stop

@section('content')
@include('shared.alerts')

@include('forms.submission')

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop