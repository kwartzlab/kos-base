@extends('adminlte::page')

@section('title', 'Reports')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
    <form id="fob-usage-form" method="GET" action="/roles">
        <div class="card-body">
            {{ csrf_field() }}
            <div class="row">
                <h3 class="card-title small">Fob Usage Report</h3>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label for="member">Member</label>
                    <select class="form-control" name="member" id="member">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->get_name() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label for="from">From</label>
                    <input type="text" class="form-control" name="from" id="from" value="{{ old('from') }}">
                </div>

                <div class="form-group col-md-3">
                    <label for="to">To</label>
                    <input type="text" class="form-control" name="to" id="to" value="{{ old('to') }}">
                </div>

                <div class="form-group col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </div>
        </div>
    </form>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
