@extends('adminlte::page')

@section('title', 'Add User Role')

@section('content_header')
    <h1>Add User Role</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="box box-primary">

  <div class="box-body">
    <form method="POST" action="/roles">

    {{ csrf_field() }}

          <div class="row">
            <div class="form-group col-md-3">
              <label for="name">Role Name</label>
              <input type="text" class="form-control" name="name" maxlength="50" id="name" value="{{ old('name') }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label for="description">Role Description</label>
              <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}">
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
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>

    </form>

  </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
<script>
  $('#acl-attributes').select2({
    placeholder: 'Select permissions to assign',
    tags: true,
    allowClear: true,
    multiple: true
  });

</script>
@stop