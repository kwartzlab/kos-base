@extends('adminlte::page')

@section('title', 'Managing ' . $team->name)

@section('content_header')
    <h1>Managing {{ $team->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
  <form method="POST" action="/teams/{{ $team->id }}">
     <div class="card-body">

      {{ method_field('PATCH') }}
      {{ csrf_field() }}

       <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" maxlength="50" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" value="@if(!old('name')){{$team->name}}@endif{{ old('name') }}">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label for="description">Description</label>
            <input type="text" class="form-control" name="description" id="description" value="@if(!old('description')){{$team->description}}@endif{{ old('description') }}">
          </div>
        </div>

        @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
        <div class="row">
          <div class="form-group col-md-6">
            <label for="{{ $team_role }}[]">{{ $team_data['name'] }}s</label>
            <select multiple class="form-control" name="{{ $team_role }}[]" id="{{ $team_role }}">
               @foreach($user_list as $key => $value)
                  <option value="{{ $key }}" 
                  <option value="{{ $key }}" @if (array_key_exists($team_role, $team_assignments)) @if (in_array($key, $team_assignments[$team_role])) selected="selected" @endif @endif>{{ $value }}</option>
               @endforeach          
            </select>
          </div>
        </div>
        @endforeach          
        
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

@section('plugins.Select2', true)
@section('js')
<script>
   $(document).ready(function () {

      @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)

      $('#{{ $team_role }}').select2({
      placeholder: 'Select members to assign role',
      tags: true,
      allowClear: true,
      multiple: true
      });
      @endforeach

   });
</script>

@endsection