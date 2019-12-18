<div class="card card-outline card-primary">
			<div class="card-header">
				<h3 class="card-title">{{ $gatekeeper->name }} </h3>
				<div class="card-tools">
               @if ($has_team)<span class="badge badge-success large">{{ $team->name }}</span>&nbsp;&nbsp;&nbsp;@endif
               <span class="badge badge-primary large">@switch($gatekeeper->type) @case('doorway')Doorway @break @case('lockout')Tool Lockout @break @case('training')Training Module @break @endswitch</span>
               &nbsp;&nbsp;&nbsp;@switch($gatekeeper->status) @case('enabled')<span class="badge badge-success large">Enabled</span> @break @case('disabled')<span class="badge badge-danger large">Disabled</span> @break @endswitch</span>
               @if ($gatekeeper->is_default == 1) &nbsp;&nbsp;&nbsp;<span class="badge badge-warning large">Default</span>@endif
            </div>
			</div>
			
			<div class="card-body">

         <div class="row">
            <div class="col-md-6">

            @if ($gatekeeper->type == 'lockout')
            <h4>Actions</h4>
               @if ($has_team)
                  @if(($team->is_lead()) || ($team->is_maintainer()))
                     <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock"></i>&nbsp;&nbsp;Lockout Tool</a>
                  @endif
               @elseif (Auth::user()->can('manage-gatekeepers'))
                  <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock"></i>&nbsp;&nbsp;Lockout Tool</a>
               @endif
            @elseif ($gatekeeper->type == 'doorway')
               <h4>Actions</h4>
               <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;5 Minutes</a>&nbsp;&nbsp;
               <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;10 Minutes</a>&nbsp;&nbsp;
               <a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/lockout" role="button"><i class="fas fa-lock-open"></i>&nbsp;&nbsp;15 Minutes</a>
            @endif
            </div>         
            @if (($gatekeeper->type == 'doorway') || ($gatekeeper->type == 'lockout'))
            <div class="col-md-3">
               <div class="info-box bg-success">
                  <span class="info-box-icon"><i class="fas fa-heartbeat"></i></span>
                  <div class="info-box-content">
                     <span class="info-box-text">Last Seen</span>
                     <span class="info-box-number">{{ $gatekeeper->last_seen->diffForHumans() }}</span>
                  </div>
               </div>            
            
            </div>
            <div class="col-md-3">
            <div class="info-box bg-warning">
                  <span class="info-box-icon"><i class="fas fa-network-wired"></i></span>
                  <div class="info-box-content">
                     <span class="info-box-text">IP Address</span>
                     <span class="info-box-number">{{ $gatekeeper->ip_address }}</span>
                  </div>
               </div>            
            </div>
            @endif

         </div>

			</div>

		</div>
