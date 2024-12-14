@php
   $teams = config('kwartzlabos.team_roles');
   $team = $gatekeeper->team()->first();
   if ($team == NULL) { $has_team = false; } else { $has_team = true; }
@endphp

<div class="card card-outline card-info" style="margin-bottom:40px;">
         <div class="card-header">
            <h3 class="card-title">{{ $gatekeeper->name }}
               @if (($gatekeeper->is_authorized()) || ($gatekeeper->is_default))
                  &nbsp;<i class="fas fa-check-circle text-success" title="You are authorized to use this tool"></i>
               @else
                  &nbsp;<i class="fas fa-ban text-danger" title="You are not authorized to use this tool"></i>
               @endif
            </h3>
            <div class="card-tools">
               @if($has_team)
                  <span class="text-lg">Managed by</span> &nbsp;<a href="/teams/{{ $team->id }}" title="View Team"><span class="badge badge-warning badge-team badge-large">{{ $team->name  }}</span></a>
               @endif

               @if ((Auth::user()->can('manage-gatekeepers')) || ($gatekeeper->is_trainer()) || ($gatekeeper->is_maintainer()) || (($has_team) && ($team->is_lead())))
                  &nbsp;&nbsp;&nbsp;<a class="btn btn-primary" href="/gatekeepers/{{ $gatekeeper->id }}/dashboard" role="button"><i class="fas fa-cog"></i>&nbsp;&nbsp;View Dashboard</a>
               @endif
            </div>
         </div>

         <div class="card-body">
            <div class="row">
               <div class="col-md-8">
                  <div class="row">
                     <div class="col-md-8">
                        @php($status = $gatekeeper->current_status()->first())
                        @include('gatekeeper.status')
                     </div>
                  </div>
                  <div class="row">
                     <div class="col">
                        @php ($trainers = $gatekeeper->trainers()->whereRelation('user', 'status', 'active')->get())
                        @if (count($trainers)>0)
                           <h5 style="margin-bottom:0px;">Trainers</h5>
                           @foreach($trainers as $trainer)
                              <a href="/members/{{ $trainer->user()->first()->id }}/profile" title="View Profile"><span class="badge badge-primary badge-large">{{ $trainer->user()->first()->get_name() }}</span></a>&nbsp;
                           @endforeach
                        @endif
                        @php ($maintainers = $gatekeeper->maintainers()->get())
                           @if (count($maintainers)>0)
                           <h5 style="margin-top:15px;margin-bottom:0px;">Maintainers</h5>
                           @foreach($maintainers as $maintainer)
                              <a href="/members/{{ $maintainer->user()->first()->id }}/profile" title="View Profile"><span class="badge badge-primary badge-large">{{ $maintainer->user()->first()->get_name() }}</span></a>&nbsp;
                           @endforeach
                        @endif
                        @if ($gatekeeper->wiki_page)
                          <div class="row" style="margin-top:15px;margin-bottom:0px;">
                            <div class="col">
                              <a href="{{ $gatekeeper->wiki_page }}" title="Kwartzlab Wiki"><i class="fab fa-wikipedia-w fa-2x circle-icon-64"></i></a>
                            </div>
                          </div>
                        @endif
                     </div>
                  </div>
               </div>
               <div class="col">
                  &nbsp;
               </div>

               <div class="col-md-2.5">
                  @if ($gatekeeper->photo != NULL)
                     <img class="profile-image img-responsive" src="<?php echo '/storage/images/gatekeepers/' . $gatekeeper->photo ?>-512px.jpeg">
                  @else
                     <img src="/img/no-gatekeeper-photo.png" class="profile-image img-responsive" class="img-square"/>
                  @endif
               </div>
            </div>

      </div>
</div>
