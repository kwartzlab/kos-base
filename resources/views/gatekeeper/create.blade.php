@extends('adminlte::page')

@section('title', 'Add Gatekeeper')

@section('content_header')
    <h1>Add Gatekeeper</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
  <form method="POST" action="/gatekeepers">
    <div class="card-body">

      {{ csrf_field() }}

       <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" value="{{ old('name') }}">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3">
            <label for="status">Type</label>
            <select class="form-control" name="type">
              <option value="doorway" @if (old('type') == 'doorway') selected="selected" @endif>Doorway</option>
              <option value="lockout" @if (old('type') == 'lockout') selected="selected" @endif>Tool Lockout</option>
              <option value="training" @if (old('type') == 'training') selected="selected" @endif>Training Module</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="status">Status</label>
            <select class="form-control" name="status">
              <option value="enabled" @if (old('status') == 'enabled') selected="selected" @endif>Enabled</option>
              <option value="disabled" @if (old('status') == 'disabled') selected="selected" @endif>Disabled</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3">
            <label for="team_id">Managed by team</label>
            <select class="form-control" name="team_id">
              <option value="0" @if ($selected_team == 0) selected="selected" @endif>None</option>
              @foreach($teams as $team)
                <option value="{{ $team->id }}" @if ($selected_team == $team->id) selected="selected" @endif>{{ $team->name }}</option>
              @endforeach
            </select>
          </div>
          </div>

        <div class="row" style="margin-top:10px;">
          <div class="form-group col-md-0.5">
            <label class="switch">
                <input type="checkbox" class="success" name="is_default" @if (old('is_default')) checked @endif>
                <span class="slider round"></span>
            </label>
          </div>
          <div class="col-md-5">
            <strong>Auto-authorize all active users for this gatekeeper</strong>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3">
            <label for="shared_auth">Use authorizations from another gatekeeper?</label>
            <select class="form-control" name="shared_auth">
              <option value="0" @if ($shared_auth == 0) selected="selected" @endif>No</option>
              @foreach($gatekeepers as $gk)
                <option value="{{ $gk->id }}" @if ($shared_auth == $gk->id) selected="selected" @endif>{{ $gk->name }}</option>
              @endforeach
            </select>
          </div>
          </div>


        <div class="form-group col-md-5">
        <label for="auth_key">Authentication Key (must match gatekeeper config)</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text"><i class="fas fa-key"></i></div>
            </div>
            <input type="text" class="form-control @if($errors->has('auth_key')) is-invalid @endif" name="auth_key" id="auth_key" value="@if(!old('auth_key')){{$auth_key}}@endif{{ old('auth_key') }}">
          </div>
        </div>

      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
      </div>
    </form>

</div>


@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
