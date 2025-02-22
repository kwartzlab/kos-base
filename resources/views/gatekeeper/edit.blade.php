@extends('adminlte::page')

@section('title', 'Editing ' . $gatekeeper->name)

@section('content_header')
    <h1>Editing {{ $gatekeeper->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
  <form method="POST" action="/gatekeepers/{{ $gatekeeper->id }}">
    <div class="card-body">

      {{ method_field('PATCH') }}
      {{ csrf_field() }}

        <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" class="form-control" name="name" id="name" value="@if(!old('name')){{$gatekeeper->name}}@endif{{ old('name') }}">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3">
            <label for="status">Type</label>
            <select class="form-control" name="type">

              @if (!old('status')))
                <option value="doorway" @if ($gatekeeper->type == 'doorway') selected="selected" @endif>Doorway</option>
                <option value="lockout" @if ($gatekeeper->type == 'lockout') selected="selected" @endif>Tool Lockout</option>
              @else
                <option value="doorway" @if (old('type') == 'doorway') selected="selected" @endif>Doorway</option>
                <option value="lockout" @if (old('type') == 'lockout') selected="selected" @endif>Tool Lockout</option>
              @endif

            </select>
          </div>
       </div>

       <div class="row">
        <div class="form-group col-md-3">
          <label for="status">Status</label>
          <select class="form-control" name="status">

            @if (!old('status')))
              <option value="enabled" @if ($gatekeeper->status == 'enabled') selected="selected" @endif>Enabled</option>
              <option value="disabled" @if ($gatekeeper->status == 'disabled') selected="selected" @endif>Disabled</option>
            @else
              <option value="enabled" @if (old('status') == 'enabled') selected="selected" @endif>Enabled</option>
              <option value="disabled" @if (old('status') == 'disabled') selected="selected" @endif>Disabled</option>
            @endif

          </select>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-3">
          <label for="team_id">Managed by team</label>
          <select class="form-control" name="team_id">
            <option value="0" @if ($shared_auth == 0) selected="selected" @endif>None</option>
            @foreach($teams as $team)
              <option value="{{ $team->id }}" @if ($selected_team == $team->id) selected="selected" @endif>{{ $team->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-3">
          <label for="name">Wiki Page</label>
          <input type="text" class="form-control" name="wiki_page" id="wiki_page" value="@if(!old('wiki_page')){{$gatekeeper->wiki_page}}@endif{{ old('wiki_page') }}">
        </div>
      </div>

      <div class="row" style="margin-top:10px;">
        <div class="form-group col-md-0.5">
          <label class="switch">
              @if (!old('is_default')))
                <input type="checkbox" class="success" name="is_default" @if ($gatekeeper->is_default == 1) checked @endif>
              @else
                <input type="checkbox" class="success" name="is_default" @if (old('is_default') == 1) checked @endif>
              @endif
              <span class="slider round"></span>
          </label>
        </div>
        <div class="col-md-5">
          <strong>Auto-authorize all active users for this gatekeeper</strong>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-4">
          <label for="shared_auth" class="col-xs-3">Use authorizations from another gatekeeper?</label>
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
          <input type="text" class="form-control" name="auth_key" id="auth_key" value="@if(!old('auth_key')){{$gatekeeper->auth_key}}@endif{{ old('auth_key') }}">
        </div>
      </div>

      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>

</div>

<div class="card card-danger card-outline">
    <div class="card-header">
      <h3 class="card-title">Delete Gatekeeper</h3>
    </div>

  <div class="card-body">

    <form method="POST" action="/gatekeepers/{{ $gatekeeper->id }}">

      {{ method_field('DELETE') }}
      {{ csrf_field() }}

      <div class="form-group">
        <p><strong>Warning: Deleting a Gatekeeper will also remove it's authentication history. This action cannot be undone!</strong></p>

        <div class="row">
          <div class="col-md-0.5">
          <label class="switch">
              <input type="checkbox" class="danger" name="confirm">
              <span class="slider round"></span>
          </label>
          </div>
          <div class="col-md-1">
          <strong>Confirm</strong>
          </div>
        </div>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-danger" id="delete_user">Delete Permanently</button>
      </div>
    </form>
  </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
