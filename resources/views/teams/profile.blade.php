<div class="card card-outline card-primary">
			<div class="card-header">
				<h3 class="card-title small">{{ $team->name }}</h3>
				<div class="card-tools">
            @if($team->is_lead())
               <a class="btn btn-primary" href="/teams/{{ $team->id }}/edit" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Team Assignments</a>
            @endif
<?php /*            @if(($team->is_trainer()) || ($team->is_lead()))
               <a class="btn btn-primary" href="/teams/{{ $team->id }}/authorizations" role="button"><i class="far fa-check-circle"></i>&nbsp;&nbsp;Manage Authorizations</a>
            @endif */ ?>
            </div>
			</div>
			
			<div class="card-body">

         <div class="row">
            <div class="col-md-6">
               @if ($team->is_member(\Auth::user()->id))
                  <p>Your Role(s):&nbsp;&nbsp;@foreach($team->assignments()->where('user_id', \Auth::user()->id)->get() as $assignment)<span class="badge badge-primary">{{ $team_roles[$assignment['team_role']]['name'] }}</span>&nbsp;&nbsp;@endforeach</p>
               @endif
               @php($gatekeepers = $team->gatekeepers()->get())
                  @if(!$gatekeepers->isEmpty())
                     <table class="table table-striped">
                        <thead><tr>
                           <th>Tool</th>
                           <th>Authorizations</th>
                           <th>Actions</th>
                        </tr></thead>
                        <tbody>
                           @foreach($gatekeepers as $gatekeeper)
                              <tr>
                                 <td>{{ $gatekeeper->name }}</td>
                                 <td>{{ $gatekeeper->count_authorizations() }}</td>
                                 <td>
                                    <a class="btn btn-primary btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/dashboard" role="button"><i class="fas fa-lock"></i>&nbsp;&nbsp;Manage</a>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
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
                  <a href="/teams/{{ $team->id }}/requests/maintenance" class="small-box-footer">View Requests&nbsp;&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
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
                  <a href="/teams/{{ $team->id }}/requests/training" class="small-box-footer">View Requests&nbsp;&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
               </div>
            </div>
         </div>

			</div>

		</div>
