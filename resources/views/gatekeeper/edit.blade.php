@extends('adminlte::page')

@section('title', 'Editing ' . $gatekeeper->name)

@section('content_header')
    <h1>Editing {{ $gatekeeper->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="box box-primary">

  <div class="box-body">


    <form method="POST" action="/gatekeepers/{{ $gatekeeper->id }}">

    {{ method_field('PATCH') }}
    {{ csrf_field() }}



      <div class="row">
        <div class="form-group col-md-3">
          <label for="name">Name</label>
          <input type="text" class="form-control" name="name" id="name" value="@if(!old('name')){{$gatekeeper->name}}@endif{{ old('name') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="description">Description</label>
          <input type="text" class="form-control" name="description" id="description" value="@if(!old('description')){{$gatekeeper->description}}@endif{{ old('description') }}">
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-3">
          <label for="status">Type</label>
          <select class="form-control" name="type">

            @if (!old('status')))
              <option value="doorway" @if ($gatekeeper->type == 'doorway') selected="selected" @endif>Doorway</option>
              <option value="lockout" @if ($gatekeeper->type == 'lockout') selected="selected" @endif>Machine Lockout</option>
            @else
              <option value="doorway" @if (old('type') == 'doorway') selected="selected" @endif>Doorway</option>
              <option value="lockout" @if (old('type') == 'lockout') selected="selected" @endif>Machine Lockout</option>
            @endif

          </select>
        </div>
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
        <div class="form-group col-md-4">
        <label for="is_default">Auto-authorize all active users for this gatekeeper?</label>
        <div class="col-xs-4 no-padding">
          <select class="form-control" style="width" name="is_default" >

          @if (!old('is_default')))
            <option value="1" @if ($gatekeeper->is_default == 1) selected="selected" @endif>Yes</option>
            <option value="0" @if ($gatekeeper->is_default == 0) selected="selected" @endif>No</option>
          @else
            <option value="1" @if (old('is_default') == 1) selected="selected" @endif>Yes</option>
            <option value="0" @if (old('is_default') == 0) selected="selected" @endif>No</option>
          @endif

          </select>
        </div>

        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-3">
          <label for="ip_address">IP Address (required for remote functions)</label>
          <div class="input-group col-xs-8">
            <div class="input-group-addon"><i class="fa fa-laptop"></i></div>
            <input type="text" class="form-control" name="ip_address" id="ip_address" value="@if(!old('ip_address')){{$gatekeeper->ip_address}}@endif{{ old('ip_address') }}">
          </div>
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-5">
        <label for="auth_key">Authentication Key (if changed, must be updated on gatekeeper)</label>
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-key"></i></div>
            <input type="text" class="form-control" name="auth_key" id="auth_key" value="@if(!old('auth_key')){{$gatekeeper->auth_key}}@endif{{ old('auth_key') }}">
          </div>
        </div>
      </div>

    <div class="form-group">
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

    </form>

  </div>
</div>

@if ($gatekeeper->is_default == 0)

<div class="box box-success">
  <div class="box-header">
    <h3 class="form-heading">Manage Trainers</h3>
    </div>

        <form method="POST" action="/gatekeepers/{{ $gatekeeper->id }}/add_trainer">

        {{ csrf_field() }}

        <div class="row" style="padding-left:10px;">
          <div class="form-group col-md-6">
            <div class="input-group col-xs-8">
              <div class="input-group-addon"><i class="fa fa-user"></i></div>
                <select class="form-control" name="user_id">
                  @foreach($user_ids as $key => $value)
                  <option value="{{ $key }}">{{$value}}</option>
                  @endforeach
                </select>
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary">Add Trainer</button>
                </span>
           </div>
         </div>
        </div>

        </form>

    <div class="box-body no-padding">
    <table class="table table-striped">
      <thead><tr>
        <th>Trainer Name</th>
        <th>Added</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
        @foreach($gatekeeper->trainers as $trainer)
          <tr>
            <td>{{ $trainer->name() }}</td>
            <td>{{ $trainer->created_at->diffForHumans() }}</td>
            <td>
            <a class="btn btn-danger btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/remove_trainer/{{ $trainer->id }}" role="button">Remove</a>

            </td>
          </tr>

        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endif

<div class="box box-danger">

  <div class="box-body">

    <h3 class="form-heading">Delete Gatekeeper</h3>

    <form method="POST" action="/gatekeepers/{{ $gatekeeper->id }}">

      {{ method_field('DELETE') }}
      {{ csrf_field() }}

      <div class="form-group">
      <p><strong>Warning: Deleting a Gatekeeper may prevent access to its authentication data. This action cannot be undone!</strong></p>

      <div class="checkbox">
          <label>
            <input type="checkbox" name="confirm"> Confirmed
          </label>
        </div>

      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-danger">Delete Permanently</button>
      </div>

      </form>

  </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
