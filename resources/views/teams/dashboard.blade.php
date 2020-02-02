@extends('adminlte::page')

@section('title', 'Dashboard - ' . $team->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-primary">
      <div class="card-header">
         <h3 class="card-title ">{{ $team->name }}</h3>
         <div class="card-tools">
         @if($team->is_lead())
            <a class="btn btn-primary" href="/teams/{{ $team->id }}/edit" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Team Assignments</a>
         @endif
         </div>
      </div>
      
      <div class="card-body">

      <div class="row">
         <div class="col-md-8">
            @if ($team->is_member(\Auth::user()->id))
               <h5>My Roles</h5>   
               <p>@foreach($team->assignments()->where('user_id', \Auth::user()->id)->get() as $assignment)
                     @if(($assignment->team_role == 'trainer') || ($assignment->team_role == 'maintainer'))
                           <span class="badge badge-primary badge-large">{{ $team_roles[$assignment->team_role]['name'] }} - {{ $assignment->gatekeeper()->first()->name }} </span>&nbsp;
                     @else
                           <span class="badge badge-primary">{{ $team_roles[$assignment->team_role]['name'] }}</span>&nbsp;
                     @endif
                  @endforeach
               </p>
            @endif
         </div>         
         <div class="col">
            &nbsp;
         </div>
         <div class="col-md-2.5">
            @if (($team->is_lead()) || (Auth::user()->can('manage-teams')))
               <div class="hovereffect-square">
                  @if ($team->photo != NULL)
                     <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/teams/' . $team->photo ?>-512px.jpeg" onerror="this.onerror=null;this.src='{{ asset('img/no-team-photo.png') }}';">   
                  @else
                     <img src="/img/no-team-photo.png" style="float:right; max-height:240px;" class="img-square"/>   
                  @endif
                  <div class="overlay">
                     <a class="img-upload" href="#" target="popup" onclick="window.open('/image-crop/teams/{{ $team->id }}','popup','width=640,height=790'); return false;"><i class="fas fa-file-upload fa-3x"></i></a>
                  </div>
               </div>
            @else
               @if ($team->photo != NULL)
                  <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/teams/' . $team->photo ?>-512px.jpeg" onerror="this.onerror=null;this.src='{{ asset('img/no-team-photo.png') }}';">
               @else
                  <img src="/img/no-team-photo.png" style="float:right; max-height:240px;" class="img-square"/> 
               @endif
            @endif
         </div>
      </div>

      </div>
</div>

<div class="card card-outline card-primary">
      <div class="card-header">
         <h3 class="card-title ">Managed Tools</h3>
         <div class="card-tools">
         </div>
      </div>
      
      <div class="card-body">
         <div class="row">
            @php($gatekeepers = $team->gatekeepers()->get())
            @if(!$gatekeepers->isEmpty())
               <table class="table table-striped">
                  <thead><tr>
                     <th>Tool</th>
                     <th>Status</th>
                     <th>Authorizations</th>
                     <th>Training Req</th>
                     <th>Maintenance Req</th>
                     <th>Actions</th>
                  </tr></thead>
                  <tbody>
                     @foreach($gatekeepers as $gatekeeper)
                        <tr>
                           <td>{{ $gatekeeper->name }}</td>
                           <td>
                              @php($status = $gatekeeper->current_status()->first())
                              @if($status != NULL)
                                 @switch($status->status)
                                    @case('online')
                                       <span class="badge badge-success">Online</span>
                                    @break
                                    @case('offline')
                                       <span class="badge badge-danger">Offline</span>
                                    @break
                                    @case('inuse')
                                       <span class="badge badge-info">In use</span>
                                    @break

                                 @endswitch
                              @else
                                 <span class="badge badge-warning">Unknown</span>
                              @endif

                           </td>
                           <td>{{ $gatekeeper->count_authorizations() }}</td>
                           <td>0</td>
                           <td>0</td>
                           <td>
                                 <a class="btn btn-primary btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/dashboard" role="button"><i class="fas fa-cog"></i>&nbsp;&nbsp;Manage</a>
                           </td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            @endif


         </div>
      </div>
   </div>


   <div class="card card-outline card-primary">
      <div class="card-header">
         <h3 class="card-title ">Team Members</h3>
         <div class="card-tools">
         </div>
      </div>
      
      <div class="card-body">
         <div class="row">
            @if(!$team_members->isEmpty())
               <table class="table table-striped">
                  <thead><tr>
                     <th>Name</th>
                     <th>Roles</th>
                     <th>Actions</th>
                  </tr></thead>
                  <tbody>
                     @foreach($team_members as $team_member)
                        <tr>
                           <td>{{ $team_member->get_name() }}</td>
                           <td>
                              <p>
                              @foreach ($team_member->team_assignments($team->id)->get() as $assignment)

                                 @if(($assignment->team_role == 'trainer') || ($assignment->team_role == 'maintainer'))
                                       <span class="badge badge-primary">{{ $team_roles[$assignment->team_role]['name'] }} - {{ $assignment->gatekeeper()->first()->name }} </span>&nbsp;
                                 @else
                                       <span class="badge badge-primary">{{ $team_roles[$assignment->team_role]['name'] }}</span>&nbsp;
                                 @endif
                              @endforeach
                              </p>
                           </td>
                           <td>
                                 <a class="btn btn-primary btn-sm" href="/members/{{ $team_member->id }}/profile" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a>
                           </td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            @endif


         </div>
      </div>
   </div>


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
         iDisplayLength: 25,
         "language": {
            "emptyTable": "No teams defined."
         }				
      });




   });
</script>
@stop