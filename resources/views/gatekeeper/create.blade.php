@extends('layout')


@section('content')


<div class="box box-primary">

  <div class="box-body">
    <form method="POST" action="/gatekeepers">

    {{ csrf_field() }}


          <div class="row">
            <div class="form-group col-md-3">
              <label for="name">Name</label>
              <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label for="description">Description</label>
              <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}">
            </div>
          </div>


          <div class="row">
            <div class="form-group col-md-3">
              <label for="status">Type</label>
              <select class="form-control" name="type">
                  <option value="doorway" @if (old('type') == 'doorway') selected="selected" @endif>Doorway</option>
                  <option value="lockout" @if (old('type') == 'lockout') selected="selected" @endif>Machine Lockout</option>
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
            <div class="form-group col-md-4">
            <label for="is_default">Auto-authorize all active users for this gatekeeper?</label>
            <div class="col-xs-4 no-padding">
              <select class="form-control" style="width" name="is_default" >
                <option value="1" @if (old('is_default') == 1) selected="selected" @endif>Yes</option>
                <option value="0" @if (old('is_default') == 0) selected="selected" @endif>No</option>
              </select>
            </div>

            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-3">
              <label for="ip_address">IP Address (required for remote functions)</label>
              <div class="input-group col-xs-8">
                <div class="input-group-addon"><i class="fa fa-laptop"></i></div>
                <input type="text" class="form-control" name="ip_address" id="ip_address" value="{{ old('ip_address') }}">
              </div>
            </div>
          </div>


          <div class="row">
            <div class="form-group col-md-5">
            <label for="auth_key">Authentication Key (unique for each gatekeeper)</label>
              <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-key"></i></div>
                <input type="text" class="form-control" name="auth_key" id="auth_key" value="@if(!old('auth_key')){{$auth_key}}@endif{{ old('auth_key') }}">
              </div>
            </div>
          </div>


        <div class="form-group">
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>

    </form>

  </div>
</div>

@endsection