@extends('adminlte::page')

@section('title', 'Managing ', $gatekeeper->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

@php($teams = config('kwartzlabos.team_roles'))

<div class="card card-outline card-primary">
         <div class="card-header">
            <h3 class="card-title">{{ $gatekeeper->name }} </h3>
            <div class="card-tools">
               @if ($has_team)<a href="/teams/{{ $team->id }}" title="View {{ $team->name }}"><span class="badge badge-warning badge-team badge-large">{{ $team->name }}</span></a>&nbsp;&nbsp;&nbsp;@endif
               <span class="badge badge-primary badge-large">@switch($gatekeeper->type) @case('doorway')Doorway @break @case('lockout')Tool Lockout @break @case('training')Training Module @break @endswitch</span>
               &nbsp;&nbsp;&nbsp;@switch($gatekeeper->status) @case('enabled')<span class="badge badge-success badge-large">Enabled</span> @break @case('disabled')<span class="badge badge-danger badge-large">Disabled</span> @break @endswitch</span>
               @if ($gatekeeper->is_default == 1) &nbsp;&nbsp;&nbsp;<span class="badge badge-warning badge-large">Default</span>@endif
            </div>
         </div>
         
         <div class="card-body">

            <div class="row">
               <div class="col-md-6">
               <div class="row">
                  <div class="col">
                     @php($status = $gatekeeper->current_status()->get()->first())
                     @include('gatekeeper.status')
                  </div>
               </div>
               <div class="row">
                  @if (($gatekeeper->type == 'doorway') || ($gatekeeper->type == 'lockout'))
                     <div class="col-md-6">
                        <div class="info-box bg-{{ $heartbeat_status }}">
                           <span class="info-box-icon"><i class="fas fa-heartbeat"></i></span>
                           <div class="info-box-content">
                              <span class="info-box-text">Last Seen</span>
                              <span class="info-box-number">@if ($gatekeeper->last_seen != NULL) {{ $gatekeeper->last_seen->diffForHumans() }} @else Never @endif</span>
                           </div>
                        </div>            
                     </div>
                     <div class="col-md-6">
                           <div class="info-box bg-warning">
                              <span class="info-box-icon text-white"><i class="fas fa-network-wired"></i></span>
                              <div class="info-box-content text-white">
                                 <span class="info-box-text">IP Address</span>
                                 <span class="info-box-number">@if($status != NULL)@if($status->ip_address == NULL)Unknown @else{{ $status->ip_address }}@endif @else Unknown @endif</span>
                              </div>
                           </div>            
                        </div>
                        
                  @endif
                  </div>

                  <div class="row">
                     <div class="col">

                        @if ($gatekeeper->type == 'lockout')
                           @if ((Auth::user()->can('manage-gatekeepers')) || ($gatekeeper->is_maintainer()) || (($has_team) && ($team->is_lead())))
                              <?php /* <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock"></i>&nbsp;&nbsp;Lockout Tool</a> */ ?>
                              @endif
                        @elseif ($gatekeeper->type == 'doorway')
                           <h4>Actions</h4>
                           <?php /*
                           <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;5 Minutes</a>&nbsp;&nbsp;
                           <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;10 Minutes</a>&nbsp;&nbsp;
                           <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;15 Minutes</a> */ ?>
                        @endif

                     </div>
                  </div>

               </div>
               <div class="col">
                  &nbsp;
               </div>

               <div class="col-md-2.5 pull-right">
                  @if((($has_team) && ($team->is_lead(\Auth::user()->id))) || (\Auth::user()->can('manage-gatekeepers')))
                     <div class="hovereffect-square">
                        @if ($gatekeeper->photo != NULL)
                           <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/gatekeepers/' . $gatekeeper->photo ?>-512px.jpeg" alt="">   
                        @else
                           <img src="/img/no-gatekeeper-photo.png" style="float:right; max-height:240px;" class="img-square"/>
                        @endif
                        <div class="overlay">
                           <a class="img-upload" href="#" target="popup" onclick="window.open('/image-crop/gatekeepers/{{ $gatekeeper->id }}','popup','width=640,height=790'); return false;"><i class="fas fa-file-upload fa-3x"></i></a>
                        </div>
                     </div>
                  @else
                     @if ($gatekeeper->photo != NULL)
                        <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/gatekeepers/' . $gatekeeper->photo ?>-512px.jpeg">
                     @else
                     <img src="/img/no-gatekeeper-photo.png" style="float:right; max-height:240px;" class="img-square"/>
                     @endif
                  @endif
               </div>
            </div>

      </div>
</div>

@if($gatekeeper->type != 'doorway')

   <div class="card card-outline card-warning">
      <div class="card-body">
         <div class="row">
            <div class="col-md-6">
            @if((($has_team) && ($team->is_lead(\Auth::user()->id))) || (\Auth::user()->can('manage-gatekeepers')))
               <div style="float:right;"><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#confirm-add-trainer"><i class="fas fa-plus-circle"></i>&nbsp;&nbsp;Add Trainer</button></div>
            @endif
            <h3>Trainers</h3>
               <div class="table-responsive">
                  <table class="table table-striped" id="trainers_table">
                     <thead><tr>
                        <th>Name</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                     </tr></thead>
                     <tbody>
                        @foreach($gatekeeper->trainers()->get() as $trainer)
                           <tr class="request_row" id="trainer-{{ $trainer->user_id }}">
                              <td>{{ $trainer->user()->get()->first()->get_name() }}</td>
                              <td>
                                 @switch($trainer->status)
                                    @case('active')
                                       {{ $trainer->updated_at->diffForHumans() }}
                                       @break
                                    @case('new')
                                       Awaiting Approval
                                       @break
                                 @endswitch

                              </td>
                              <td>
                                 <a href="/members/{{ $trainer->user->id }}/profile/" class="btn btn-primary btn-sm" id="btnprofile" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a>&nbsp;&nbsp;
                                 @if((($has_team) && ($team->is_lead(\Auth::user()->id))) || (\Auth::user()->can('manage-gatekeepers')))
                                    <button class="btn btn-danger btn-sm remove_button" data-record-id="{{ $trainer->user->id }}" data-toggle="modal" data-target="#confirm-delete-trainer"><i class="fas fa-ban"></i> Remove</button>
                                 @endif
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
            @if($gatekeeper->type != 'training')
            <div class="col-md-6">
            @if((($has_team) && ($team->is_lead(\Auth::user()->id))) || (\Auth::user()->can('manage-gatekeepers')))
               <div style="float:right;"><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#confirm-add-maintainer"><i class="fas fa-plus-circle"></i>&nbsp;&nbsp;Add Maintainer</button></div>
            @endif
            <h3>Maintainers</h3>
            <div class="table-responsive">
                  <table class="table table-striped">
                     <thead><tr>
                        <th>Name</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                     </tr></thead>
                     <tbody>
                        @foreach($gatekeeper->maintainers()->get() as $maintainer)
                           <tr class="request_row" id="maintainer-{{ $maintainer->user_id }}">
                              <td>{{ $maintainer->user()->get()->first()->get_name() }}</td>
                              <td>
                                 @switch($maintainer->status)
                                    @case('active')
                                       {{ $maintainer->updated_at->diffForHumans() }}
                                       @break
                                    @case('new')
                                       Awaiting Approval
                                       @break
                                 @endswitch

                              </td>
                              <td>
                                 <a href="/members/{{ $maintainer->user->id }}/profile/" class="btn btn-primary btn-sm" id="btnprofile" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a>&nbsp;&nbsp;
                                 @if((($has_team) && ($team->is_lead(\Auth::user()->id))) || (\Auth::user()->can('manage-gatekeepers')))
                                    <button class="btn btn-danger btn-sm remove_button" data-record-id="{{ $maintainer->user->id }}" data-toggle="modal" data-target="#confirm-delete-maintainer"><i class="fas fa-ban"></i> Remove</button>
                                 @endif
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
            @endif
         </div>
      </div>
   </div>

@endif

@if($gatekeeper->is_default != 1)
   @if((\Auth::user()->can('manage-gatekeepers')) || ($gatekeeper->is_trainer()) || ($gatekeeper->is_maintainer()) || (($has_team) && ($gatekeeper->team()->first()->is_member())))
      <div class="card card-outline card-primary">
         <div class="card-header">
            <h3 class="card-title">Authorizations</h3>
            <div class="card-tools">
            </div>
         </div>
         
         <div class="card-body">
         @if($gatekeeper->shared_auth == 0)
               @if($gatekeeper->is_trainer())
               <form method="POST" action="/gatekeepers/authorize">
               
               <input name="gatekeeper_id" type="hidden" value="{{ $gatekeeper->id }}">
               {{ csrf_field() }}

               <div class="row">
                     <div class="form-group col-md-5">
                           <div class="input-group">
                              <div class="input-group-prepend">
                                 <div class="input-group-text"><i class="fas fa-user"></i></div>
                              </div>

                              <select class="form-control" name="user_id">
                                 @foreach($all_users as $user_id => $user_data)
                                    @if(!$user_data['is_authorized'])
                                    <option value="{{ $user_id }}">{{$user_data['name']}}</option>
                                    @endif
                                 @endforeach
                              </select>

                              <span class="input-group-btn">
                                 <button type="submit" class="btn btn-primary">Add User</button>
                              </span>
                           </div>
                     </div>
                  </div>
               </form>
               @endif

               <div class="table-responsive">
                  <table class="table table-striped" id="data-table">
                     <thead><tr>
                        <th>Name</th>
                        <th>Date Authorized</th>
                        <th>Actions</th>
                     </tr></thead>
                     <tbody>
                        @foreach($authorizations as $gkauth)
                           <tr class="request_row" id="authorization-{{ $gkauth->id }}">
                              <td>{{ $gkauth->username() }}</td>
                              <td>{{ $gkauth->created_at->diffForHumans() }}</td>
                              <td>
                                 <a href="/members/{{ $gkauth->user->id }}/profile/" class="btn btn-primary btn-sm" id="btnprofile" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a>&nbsp;&nbsp;
                                 @if($gatekeeper->is_trainer())
                                    <button class="btn btn-danger btn-sm revoke_button" data-record-id="{{ $gkauth->id }}" data-record-title="{{ $gkauth->username() }}" data-toggle="modal" data-target="#confirm-delete-authorization"><i class="fas fa-ban"></i> Revoke</button>
                                 @endif
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            @else
               <h5>This tool is using authorizations from <a href="/gatekeepers/{{ \App\Gatekeeper::find($gatekeeper->shared_auth)->id }}/dashboard" title="View Tool"><span class="badge badge-info badge-large">{{ \App\Gatekeeper::find($gatekeeper->shared_auth)->name }}</span></a></h5>
            @endif
            </div>
         </div>
   @endif
@endif


<div class="modal fade" id="confirm-add-trainer" tabindex="-1" role="dialog" aria-labelledby="modal-add-trainer" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-add-trainer">Add New Trainer</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
         @if($teams['trainer']['approval_required'])
            <p>Select the name of the user you wish to add below.</p><p>Trainers require approval before they can begin their duties - once that happens they will have access to this gatekeeper's training interface and authorizations.</p>
         @else
            <p>Select the name of the user you wish to add below.</p><p>They will immediately have access to this gatekeeper's training interface and authorizations.</p>
         @endif
         <div class="input-group">
            <div class="input-group-prepend">
               <div class="input-group-text"><i class="fas fa-user"></i></div>
            </div>
               <select class="form-control" name="user_id" id="trainer_id_select">
               @foreach($all_users as $user_id => $user_data)
                  @if (!$user_data['is_trainer'])
                  <option value="{{ $user_id }}">{{$user_data['name']}}</option>
                  @endif
               @endforeach
            </select>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-ok">Add Trainer</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="confirm-add-maintainer" tabindex="-1" role="dialog" aria-labelledby="modal-add-maintainer" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-add-maintainer">Add New Maintainer</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
         @if($teams['maintainer']['approval_required'])
            <p>Select the name of the user you wish to add below.</p><p>Maintainers require approval before they can begin their duties - once that happens they will have access to this gatekeeper's maintenance options.</p>
         @else
            <p>Select the name of the user you wish to add below.</p><p>They will immediately have access to this gatekeeper's maintenance options.</p>
         @endif
         <div class="input-group">
            <div class="input-group-prepend">
               <div class="input-group-text"><i class="fas fa-user"></i></div>
            </div>
               <select class="form-control" name="user_id" id="maintainer_id_select">
               @foreach($all_users as $user_id => $user_data)
                  @if (!$user_data['is_maintainer'])
                  <option value="{{ $user_id }}">{{$user_data['name']}}</option>
                  @endif
               @endforeach
            </select>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-ok">Add Maintainer</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="confirm-delete-trainer" tabindex="-1" role="dialog" aria-labelledby="modal-remove-trainer" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-remove-trainer">Confirm Trainer Removal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to remove this trainer? They will need to be re-approved if added again later.</p>
        <p>Do you want to proceed?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Remove</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm-delete-maintainer" tabindex="-1" role="dialog" aria-labelledby="modal-remove-maintainer" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
       <div class="modal-header">
        <h4 class="modal-title" id="modal-remove-maintainer">Confirm Maintainer Removal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to remove this maintainer?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Remove</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm-delete-authorization" tabindex="-1" role="dialog" aria-labelledby="modal-remove-authorization" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-2x fa-sync fa-spin"></i>
      </div>
       <div class="modal-header">
        <h4 class="modal-title" id="modal-remove-authorization">Revoke Authorization</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to revoke <b i class="title"></b>'s authorization for this gatekeeper?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Revoke</button>
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
         ordering: true,
         pagingType: "simple_numbers",
         iDisplayLength: 25,
         "language": {
            "emptyTable": "No authorizations."
         }				
      });

         $('#confirm-add-trainer').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var user_id = $("#trainer_id_select").val()

            event.preventDefault();
            
            $overlayDiv.removeClass('invisible')
            
            $.post('/gatekeepers/{{ $gatekeeper->id }}/add_trainer/' + user_id).then(function() {
               $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
               $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
               window.setTimeout(function(){ 
                  $modalDiv.modal('hide')
                  location.reload(true);
               }, 1500);
            });
         });

         $('#confirm-add-trainer').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         


         $('#confirm-delete-trainer').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var id = $(this).data('recordId');
            var row_id = '#trainer-' + id;

            $overlayDiv.removeClass('invisible')

            $.post('/gatekeepers/{{ $gatekeeper->id }}/remove_trainer/' + id).then(function() {
               $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
               $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
               window.setTimeout(function(){ 
                  $modalDiv.modal('hide')
                  $(row_id).fadeOut('slow', function(here){ 
                     $(row_id).remove();                    
                  });
               }, 1500);

               
            });
         });

         $('#confirm-delete-trainer').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         


         $('#confirm-add-maintainer').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var user_id = $("#maintainer_id_select").val()

            event.preventDefault();
            $overlayDiv.removeClass('invisible')

            $.post('/gatekeepers/{{ $gatekeeper->id }}/add_maintainer/' + user_id).then(function() {
               $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
               $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
               window.setTimeout(function(){ 
                  $modalDiv.modal('hide')
                  location.reload(true);
               }, 1500);
            });
         });

         $('#confirm-add-maintainer').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         

         $('#confirm-delete-maintainer').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var id = $(this).data('recordId');
            var row_id = '#maintainer-' + id;

            $overlayDiv.removeClass('invisible')

            $.post('/gatekeepers/{{ $gatekeeper->id }}/remove_maintainer/' + id).then(function() {
               $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
               $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
               window.setTimeout(function(){ 
                  $modalDiv.modal('hide')
                  $(row_id).fadeOut('slow', function(here){ 
                     $(row_id).remove();                    
                  });
               }, 1500);
             });
         });

         $('#confirm-delete-maintainer').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         

         $('#confirm-delete-authorization').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var id = $(this).data('recordId');
            var row_id = '#authorization-' + id;

            $overlayDiv.removeClass('invisible')

             $.post('/gatekeepers/revoke/' + id).then(function() {
               $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
               $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
               window.setTimeout(function(){ 
                  $modalDiv.modal('hide')
                  $(row_id).fadeOut('slow', function(here){ 
                     $(row_id).remove();                    
                  });
               }, 1500);
             });
         });

         $('#confirm-delete-authorization').on('show.bs.modal', function(e) {
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