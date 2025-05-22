@extends('adminlte::page')

@section('title', 'Report - ' . $report_name)

@section('content_header')
    <h1>Report - {{ $report_name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-primary">
    <div class="card-body">
        <form action="{{ url()->current() }}" method="GET">
            @csrf

            @if (in_array('daterange', $filters))
            <div class="row">
                <div class="col">
                    <label for="fromDate">From</label>
                    <input id="fromDate" name="fromDate" type="date" class="form-control" value="{{ old('fromDate') }}">
                </div>
                <div class="col">
                    <label for="toDate">To</label>
                    <input id="toDate" name="toDate" type="date" class="form-control" value="{{ old('toDate') }}">
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
