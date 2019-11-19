@extends('adminlte::page')

@section('title', 'Membership Register - ' . $user['first_name'] . ' ' . $user['last_name'])

@section('content_header')
    <h1>Membership Register</h1>
@stop

@section('content')
@include('shared.alerts')

@include('users.profile')

<div class="box box-primary">

<div class="box-body">

<form method="POST" action="/users/{{ $user->id }}" enctype="multipart/form-data">

{{ method_field('PATCH') }}
{{ csrf_field() }}

  <div class="row">
    <div class="form-group col-md-3">
      <label for="first_name">First Name</label><input type="text" class="form-control" name="first_name" id="first_name" value="@if(!old('first_name')){{$user->first_name}}@endif{{ old('first_name') }}">
    </div>
    <div class="form-group col-md-3">
      <label for="last_name">Last Name</label><input type="text" class="form-control" name="last_name" id="last_name" value="@if(!old('last_name')){{$user->last_name}}@endif{{ old('last_name') }}">
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-3">
      <label for="status">Status</label>
      <select class="form-control" name="status">

      @if (!old('status')))
        <option value="active" @if ($user->status == 'active') selected="selected" @endif>Active</option>
        <option value="applicant" @if ($user->status == 'applicant') selected="selected" @endif>Applicant</option>
        <option value="hiatus" @if ($user->status == 'hiatus') selected="selected" @endif>On Hiatus</option>
        <option value="inactive" @if ($user->status == 'inactive') selected="selected" @endif>Withdrawn</option>
      @else
        <option value="active" @if (old('status') == 'active') selected="selected" @endif>Active</option>
        <option value="applicant" @if (old('status') == 'applicant') selected="selected" @endif>Applicant</option>
        <option value="hiatus" @if (old('status') == 'hiatus') selected="selected" @endif>On Hiatus</option>
        <option value="inactive" @if (old('status') == 'inactive') selected="selected" @endif>Withdrawn</option>
      @endif

      </select>
    </div>
    <div class="form-group col-md-3">
      <label for="status">Access Level</label>
      <select class="form-control" name="acl">

      @if (!old('acl')))
        <option value="user" @if ($user->acl == 'user') selected="selected" @endif>User</option>
        <option value="keyadmin" @if ($user->acl == 'keyadmin') selected="selected" @endif>Key Admin</option>
        <option value="admin" @if ($user->acl == 'admin') selected="selected" @endif>Superuser</option>
      @else
        <option value="user" @if (old('acl') == 'user') selected="selected" @endif>User</option>
        <option value="keyadmin" @if (old('acl') == 'keyadmin') selected="selected" @endif>Key Admin</option>
        <option value="admin" @if (old('acl') == 'admin') selected="selected" @endif>Superuser</option>
      @endif

      </select>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-2">
      <label for="date_applied">Date Applied</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
        <input type="text" class="form-control datepicker" name="date_applied" id="date_applied" value="@if(!old('date_applied')){{$user->date_applied}}@endif{{ old('date_applied') }}">
      </div>
    </div>
    <div class="form-group col-md-2">
      <label for="date_admitted">Date Admitted</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
        <input type="text" class="form-control datepicker" name="date_admitted" id="date_admitted" value="@if(!old('date_admitted')){{$user->date_admitted}}@endif{{ old('date_admitted') }}">
      </div>
    </div>
    <div class="form-group col-md-2">
      <label for="date_withdrawn">Date Withdrawn</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
        <input type="text" class="form-control datepicker" name="date_withdrawn" id="date_withdrawn" value="@if(!old('date_withdrawn')){{$user->date_withdrawn}}@endif{{ old('date_withdrawn') }}">
      </div>
    </div>

    <div class="form-group col-md-2">
      <label for="date_hiatus_start">Hiatus Start Date</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
        <input type="text" class="form-control datepicker" name="date_hiatus_start" id="date_hiatus_start" value="@if(!old('date_hiatus_start')){{$user->date_hiatus_start}}@endif{{ old('date_hiatus_start') }}">
      </div>
    </div>
    <div class="form-group col-md-2">
      <label for="date_hiatus_end">Hiatus End Date</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
        <input type="text" class="form-control datepicker" name="date_hiatus_end" id="date_hiatus_end" value="@if(!old('date_hiatus_end')){{$user->date_hiatus_end}}@endif{{ old('date_hiatus_end') }}">
      </div>
    </div>
</div>

<div class="row">
  <div class="form-group col-md-6">
    <label for="photo">Upload new Member Photo (6 MB max size)</label>
    <input type="file" class="form-control" name="photo" id="photo" />

  </div>
</div>

<h3 class="form-heading">Contact Info</h3>

  <div class="row">
    <div class="form-group col-md-3">
      <div class="input-group">
          <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
        <input type="email" class="form-control" name="email" id="email" value="@if(!old('email')){{$user->email}}@endif{{ old('email') }}">
      </div>
    </div>
    <div class="form-group col-md-3">
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-phone"></i></div>
          <input type="text" class="form-control" name="phone" id="phone" value="@if(!old('phone')){{$user->phone}}@endif{{ old('phone') }}">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label for="address">Street Address</label>
      <input type="text" class="form-control" name="address" id="address" value="@if(!old('address')){{$user->address}}@endif{{ old('address') }}">

    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-2">
      <label for="city">City</label>
      <input type="text" class="form-control" name="city" id="city" value="@if(!old('city')){{$user->city}}@endif{{ old('city') }}">
    </div>
    <div class="form-group col-md-2">
      <label for="province">Province</label>
      <input type="text" class="form-control" name="province" id="province" value="@if(!old('province')){{$user->province}}@endif{{ old('province') }}">
    </div>
    <div class="form-group col-md-2">
      <label for="postal">Postal Code</label>
      <input type="text" class="form-control" name="postal" id="postal" value="@if(!old('postal')){{$user->postal}}@endif{{ old('postal') }}">
    </div>
  </div>


<h3 class="form-heading">Change Password</h3>

  <div class="row">
    <div class="form-group col-md-3">
    <label for="password">Password (leave blank if unchanged)</label>
    <div class="input-group">
      <div class="input-group-addon"><i class="fa fa-key"></i></div>
        <input type="password" class="form-control" name="password" id="password">

      </div>
    </div>
    <div class="form-group col-md-3">
      <label for="password_confirmation">Confirm Password</label>
      <div class="input-group">
        <div class="input-group-addon"><i class="fa fa-key"></i></div>
        <input type="password" class="form-control" name="password_confirmation" id="password">
      </div>
    </div>
  </div>

<div class="form-group">
  <button type="submit" class="btn btn-primary">Save Changes</button>
</div>

</form>
</div>
</div>


<div class="box box-success">
<div class="box-header">
<h3 class="form-heading">Manage Keys</h3>
<a class="btn btn-primary" href="/users/{{ $user->id }}/create_key/" role="button">Add Key</a>
</div>

<div class="box-body no-padding">
<table class="table table-striped">
  <thead><tr>
    <th>Hash</th>
    <th>Description</th>
    <th>Added</th>
    <th>Actions</th>
  </tr></thead>
  <tbody>
    @foreach($user->keys as $key)
      <tr>
        <td>{{ $key->rfid }}</td>
        <td>{{ $key->description }}</td>
        <td>{{ $key->created_at->diffForHumans() }}</td>
        <td>
        <a class="btn btn-danger btn-sm" href="/users/{{ $user->id }}/destroy_key/{{ $key->id }}" role="button">Delete</a>

        </td>
      </tr>

    @endforeach
  </tbody>
</table>
</div>
</div>

@if ($user->status == 'applicant'):
<div class="box box-danger">

  <div class="box-body">

    <h3 class="form-heading">Delete User</h3>

    @if($user->id != \Auth::user()->id)

    <form method="POST" action="/users/{{ $user->id }}">

    {{ method_field('DELETE') }}
    {{ csrf_field() }}

    <div class="form-group">
    <p><strong>Warning: this action cannot be undone!</strong></p>

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

    @else

    <p>You cannot delete yourself.</p>

    @endif
  </div>
</div>

@endif

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script src="/js/jquery.inputmask.bundle.min.js"></script>

   <script>
    $(document).ready(function(){
      $("#phone").inputmask("(999) 999-9999");
      $("#postal").inputmask("A9A 9A9");
      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
      });
      $('#user-roles').select2({
        placeholder: 'Select roles to assign',
        tags: true,
        allowClear: true,
        multiple: true
      });

    });
  </script>
@stop

