@extends('adminlte::page')

@section('title', 'New Form')

@section('content_header')
    <h1>New Form</h1>
@stop

@section('content')
@include('shared.alerts')

@include('forms.formbuilder')

@stop

