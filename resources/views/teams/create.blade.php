@extends('adminlte::page')

@section('title', 'New Team')

@section('content_header')
    <h1>New Team</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
  <form method="POST" action="/teams">
    <div class="card-body">

      {{ csrf_field() }}

       <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" value="{{ old('name') }}">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label for="description">Description</label>
            <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}">
          </div>
        </div>


        @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
        <div class="row">
          <div class="form-group col-md-6">
            <label for="{{ $team_role }}[]">{{ $team_data['name'] }}s</label>
            <select multiple class="form-control" name="{{ $team_role }}[]" id="{{ $team_role }}">
               @foreach($user_list as $key => $value)
                  <option value="{{ $key }}" @if(old($team_role, null) != null) @if (in_array($key, old($team_role))) selected="selected" @endif @endif>{{ $value }}</option>
               @endforeach          
            </select>
          </div>
        </div>
        @endforeach          


      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Create Team</button>
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