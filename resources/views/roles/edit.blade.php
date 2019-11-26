@extends('adminlte::page')

@section('title', 'Edit User Role')

@section('content_header')
    <h1>Editing {{ $role->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

@if ($role->id != 1)
  <div class="box box-primary">

    <div class="box-body">

      <form method="POST" action="/roles/{{ $role->id }}">

      {{ method_field('PATCH') }}
      {{ csrf_field() }}

            <div class="row">
              <div class="form-group col-md-3">
                <label for="name">Role Name</label>
                <input type="text" class="form-control" name="name" maxlength="50" id="name" value="@if(!old('name')){{$role->name}}@endif{{ old('name') }}">
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label for="description">Role Description</label>
                <input type="text" class="form-control" name="description" id="description" value="@if(!old('description')){{$role->description}}@endif{{ old('description') }}">
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label for="acl-attributes">Permissions</label>
                <select multiple class="form-control" name="acl-attributes[]" id="acl-attributes">
                
                @foreach(config('acl.permissions') as $acl_object => $acl_operations)
                  @foreach($acl_operations as $acl_operation)
                      <option value="{'{{ $acl_object }}':'{{ $acl_operation }}'}" @if (array_key_exists($acl_object . ':' . $acl_operation, $permissions)) selected="selected" @endif>[{{ $acl_object }}] {{ $acl_operation }}</option>
                  @endforeach          
                @endforeach          

                </select>
              </div>
            </div>
            
          <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>

      </form>

    </div>
  </div>
@endif

<div class="box box-success">
  <div class="box-header">
    <h3 class="form-heading">Manage Users</h3>
    </div>

        <form method="POST" action="/roles/{{ $role->id }}/add_user">

        {{ csrf_field() }}

        <div class="row" style="padding-left:10px;">
          <div class="form-group col-md-6">
            <div class="input-group col-xs-8">
              <div class="input-group-addon"><i class="fas fa-user"></i></div>
              <select class="form-control" name="assign-user" id="assign-user">
               
                @foreach($user_list as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                @endforeach          
  
               </select>
                  <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary">Add User</button>
                </span>
           </div>
         </div>
        </div>

        </form>

    <div class="box-body">
    <table class="table table-striped" id="data-table">
      <thead><tr>
        <th>User Name</th>
        <th>Added</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
        @foreach($assigned_list as $assigned_user)
          <tr>
            <td>{{ $assigned_user['name'] }}</td>
            <td>{{ $assigned_user['created_at'] }}</td>
            <td>
              <a class="btn btn-danger btn-sm" href="/roles/{{ $role->id }}/remove_user/{{ $assigned_user['id'] }}" role="button">Remove</a>

            </td>


         </tr>

        @endforeach
      </tbody>
    </table>
  </div>
</div>

@if ($role->id != 1)


  <div class="box box-danger">

    <div class="box-body">

      <h3 class="form-heading">Delete User Role</h3>

      <form method="POST" action="/roles/{{ $role->id }}">

        {{ method_field('DELETE') }}
        {{ csrf_field() }}

        <div class="form-group">
        <p><strong>Warning: This action cannot be undone!</strong></p>

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
@endif

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
<script>
  $(document).ready(function () {
    $('#data-table').dataTable({
      ordering: false,
      paging: false,
      searching: false,
      iDisplayLength: 25,
      "language": {
        "emptyTable": "No authorized users."
      }				
    });

    $('#acl-attributes').select2({
      placeholder: 'Select permissions to assign',
      tags: true,
      allowClear: true,
      multiple: true
    });

    $('#assign-user').select2({
      placeholder: 'Select user to assign role',
    });

  });

</script>
@stop