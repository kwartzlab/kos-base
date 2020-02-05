@extends('adminlte::page')

@section('title', 'Manage Teams')

@section('content_header')
    <h1>Manage Teams</h1>
@stop

@section('content')
@include('shared.alerts')


<div class="card card-outline card-primary">
   <div class="card-header">
      <h4 class="card-title">Team Assignment Requests</h4>	
      <div class="card-tools">
      </div>
   </div>
   
   <div class="card-body">
      @if(count($team_assignments) > 0)
      <div class="table-responsive">
         <table class="table table-striped" id="data-table">
            <thead><tr>
               <th>Gatekeeper</th>
               <th>User</th>
               <th>Team Assignment</th>
               <th>Actions</th>
            </tr></thead>
            <tbody>
               @foreach($team_assignments as $assignment)
                  <tr class="request_row" id="request-{{ $assignment->id }}">
                     <td>{{ $assignment->gatekeeper()->first()->name }}</td>
                     <td>{{ $assignment->user()->first()->get_name() }}</td>
                     <td>{{ config('kwartzlabos.team_roles.' . $assignment->team_role . '.name') }}</td>
                     <td>
                        <button class="btn btn-success btn-sm approve_button" id="{{ $assignment->id }}" role="button"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Approve</button>
                        &nbsp;<button class="btn btn-danger btn-sm confirm-remove-request" data-record-id="{{ $assignment->id }}" data-toggle="modal" data-target="#confirm-remove-request"><i class="fas fa-ban"></i>&nbsp;&nbsp;Remove</button>
                     </td>
                  </tr>
         
               @endforeach
            </tbody>
         </table>
      </div>
      @else
         <p>No pending assignment requests.</p>
      @endif
   </div>
</div>


<div class="card card-outline card-success">
   <div class="card-header">
      <h4 class="card-title">Team Administration</h4>	
      <div class="card-tools">
         <a class="btn btn-primary" href="/teams/create" role="button">New Team</a>
      </div>
   </div>
   
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-striped table-responsive" id="data-table">
            <thead><tr>
               <th>Name</th>
               @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
                  @if (($team_data['is_admin']) || ($team_data['is_trainer']) || ($team_data['is_maintainer']))
                     <th>{{ $team_data['name'] }}s</th>
                  @endif
               @endforeach
               <th>Actions</th>
            </tr></thead>
            <tbody>
               @foreach($teams as $team)
                  <tr>
                     <td>{{ $team->name }}</td>
                     @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
                        @if (($team_data['is_admin']) || ($team_data['is_trainer']) || ($team_data['is_maintainer']))
                           <td>@php($role_members = $team->get_role_members($team_role))@if ($role_members != false) @foreach ($role_members as $role_member)<a href="/members/{{ $role_member->user->id }}/profile" TITLE="View Profile"><span class="badge @if($role_member->user->status != 'active') badge-danger @else badge-primary @endif">{{ $role_member->user->get_name() }}</a></span> @endforeach @endif &nbsp;</td>
                        @endif
                     @endforeach
                     <td style="min-width:220px;">
                        <a class="btn btn-primary btn-sm" href="/teams/{{ $team->id }}/dashboard" role="button"><i class="fas fa-cog"></i>&nbsp;&nbsp;Manage</a>&nbsp;
                        <a class="btn btn-primary btn-sm" href="/teams/{{ $team->id }}/edit" role="button"><i class="fas fa-user-check"></i>&nbsp;&nbsp;Assignments</a>
                     </td>
                  </tr>
         
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
</div>

<div class="modal fade" id="confirm-remove-request" tabindex="-1" role="dialog" aria-labelledby="modal-remove-request" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modal-remove-request">Confirm Assignment Request Removal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to remove this team assignment request?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Remove</button>
      </div>
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

      $('.approve_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(this).attr('id');
         button_id = '#' + request_id
         row_id = '#request-' + request_id;

         jQuery.ajax({
            url: "{{ url('/gatekeepers') }}" + '/assignments/approve/' + request_id,
            method: 'post',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Approved</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }
               $(row_id).fadeOut('slow', function(here){ 
                     $(row_id).remove();                    
                  });
            }}); 
      });

      $('#confirm-remove-request').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var id = $(this).data('recordId');
            var row_id = '#request-' + id;

             $modalDiv.addClass('loading');
             $.post('/gatekeepers/assignments/remove/' + id).then(function() {
                  $modalDiv.modal('hide').removeClass('loading');
                  $(row_id).fadeOut('slow', function(here){ 
                     $(row_id).remove();                    
                  });
                  
             });
         });

         $('#confirm-remove-request').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         

         $.ajaxSetup({
            headers: {
               'Content-Type': 'application/json',
               'Accept': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
         });

   });
</script>
@stop