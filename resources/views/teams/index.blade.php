@extends('adminlte::page')

@section('title', 'Teams')

@section('content_header')
    <h1>My Teams</h1>
@stop

@section('content')
@include('shared.alerts')

@if(count($my_teams)>0)
   @foreach($my_teams as $team)
      @include('teams.profile')
   @endforeach
@else
   <div class="card card-outline card-warning">
      <div class="card-body">
         <h4>You are not assigned to any team.</h4>
      </div>
   </div>
@endif

<h2 style="margin-bottom:20px;">Other Teams</h2>

@if(count($teams)>0)
   @foreach($teams as $team)
      @if(!$team->is_member())
         @include('teams.profile')
      @endif
   @endforeach
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
         pagingType: "simple_numbers",
         iDisplayLength: {{ config('kwartzlabos.results_per_page.default') }},
         "language": {
            "emptyTable": "No teams defined."
         }
      });
   });
</script>
@stop
