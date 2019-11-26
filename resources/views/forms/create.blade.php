@extends('adminlte::page')

@section('title', 'New Form')

@section('content_header')
    <h1>New Form</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="box box-primary">

  <div class="box-body">
    <form method="POST" action="/roles">

    {{ csrf_field() }}

          <div class="row">
            <div class="form-group col-md-3">
              <label for="name">Form Name</label>
              <input type="text" class="form-control" name="name" maxlength="50" id="name" value="{{ old('name') }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label for="description">Form Description</label>
              <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}">
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label for="status">Form Status</label>
              
            </div>
          </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save Form</button>
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