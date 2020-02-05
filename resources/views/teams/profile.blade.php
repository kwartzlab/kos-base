<div class="card card-outline card-primary" style="margin-bottom:40px;">
         <div class="card-header">
            <h3 class="card-title ">{{ $team->name }}</h3>
            <div class="card-tools">
            @if(($team->is_member()) || (Auth::user()->can('manage-teams')))
               <a class="btn btn-primary" href="/teams/{{ $team->id }}/dashboard" role="button"><i class="fas fa-users"></i>&nbsp;&nbsp;Team Dashboard</a>
            @endif
            </div>
         </div>
         
         <div class="card-body">

            @if($team->is_member())
               <div class="row">
               <div class="col-md-6">
                  @if ($team->is_member(\Auth::user()->id))
                     <h5>My Roles</h5>   
                     <p>@foreach($team->assignments()->where('user_id', \Auth::user()->id)->get() as $assignment)
                           @if(($assignment->team_role == 'trainer') || ($assignment->team_role == 'maintainer'))
                                 <span class="badge badge-primary badge-large">{{ $team_roles[$assignment->team_role]['name'] }} - {{ $assignment->gatekeeper()->first()->name }} </span>&nbsp;
                           @else
                                 <span class="badge badge-primary badge-large">{{ $team_roles[$assignment->team_role]['name'] }}</span>&nbsp;
                           @endif
                        @endforeach
                     </p>
                  @endif
               </div>         
               <div class="col-md-3">
                  @php($maintenance_requests = $team->requests()->where('request_type','maintenance')->count())
                  <div class="small-box @if($maintenance_requests == 0) bg-success @elseif($maintenance_requests>4) bg-danger @else bg-warning @endif">
                     <div class="inner">
                        <h3>{{ $maintenance_requests }}</h3>
                        <p>Maintenance Requests</p>
                     </div>
                     <div class="icon">
                        <i class="fas fa-tools"></i>
                     </div>
                     <?php /* <a href="/teams/{{ $team->id }}/requests/maintenance" class="small-box-footer">View Requests&nbsp;&nbsp;<i class="fas fa-arrow-circle-right"></i></a> */ ?>
                  </div>
               </div>
               <div class="col-md-3">
                  @php($training_requests = $team->requests()->where(['request_type' =>'training','status' => 'new'])->count())
                  
                  <div class="small-box @if($training_requests == 0) bg-success @elseif($training_requests>4) bg-danger @else bg-warning @endif">
                     <div class="inner">
                        <h3>{{ $training_requests }}</h3>
                        <p>Training Requests</p>
                     </div>
                     <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                     </div>
                     <?php /* <a href="/teams/{{ $team->id }}/requests/training" class="small-box-footer">View Requests&nbsp;&nbsp;<i class="fas fa-arrow-circle-right"></i></a> */ ?>
                  </div>
               </div>
            </div>
            @if (($team->is_trainer()) || ($team->is_maintainer()))
               @php($gatekeepers = $team->gatekeepers()->get())
                  @if(!$gatekeepers->isEmpty())
                     <div class="row">
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
                                 @if (($gatekeeper->is_trainer()) || ($gatekeeper->is_maintainer()))
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
                                 @endif
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  @endif
               @endif
            @else

               <div class="row">
                  <div class="col-md-8">
                     <h5>Team Lead(s)</h5>   
                     @foreach($team->leads()->get() as $lead)
                        <a href="/members/{{ $lead->id }}/profile" title="View Profile"><span class="badge badge-primary badge-large">{{ $lead->get_name() }}</span></a>&nbsp;
                     @endforeach
                     <h5 style="margin-top:15px;">Members</h5>   
                     @foreach($team->members()->get()->unique() as $member)
                        @if (!$team->is_lead($member->id))
                        <a href="/members/{{ $member->id }}/profile" title="View Profile"><span class="badge badge-primary badge-large">{{ $member->get_name() }}</span></a>&nbsp;
                        @endif
                     @endforeach
                     <h5 style="margin-top:25px;">Managed Tools</h5>   
                     @foreach($team->gatekeepers()->get() as $gatekeeper)
                        <a href="/gatekeepers/{{ $gatekeeper->id }}" title="View Tool"><span class="badge badge-info badge-large">{{ $gatekeeper->name }}</span></a>&nbsp;
                     @endforeach
                  </div>
                  <div class="col">
                     &nbsp;
                  </div>
                  <div class="col-md-2.5">
                     @if ($team->photo != NULL)
                        <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/teams/' . $team->photo ?>-512px.jpeg" onerror="this.onerror=null;this.src='{{ asset('img/no-team-photo.png') }}';">
                     @else
                        <img src="/img/no-team-photo.png" style="float:right; max-height:240px;" class="img-square"/> 
                     @endif
                  </div>
               </div>
            @endif


         </div>

      </div>
