<div class="card card-warning card-outline member-profile">
   <div class="card-body">

      <div class="row">
         <div class="col-md-9">
            <h2>{{ $user->first_name }} {{ $user->last_name }}</h2>

            @if($user->status == 'active')
               <h5>Member Since {{ $user->date_admitted->toDateString() }}</h5>
            @elseif($user->status == 'inactive')
               <h5>Withdrawn {{ $user->date_withdrawn->toDateString() }}</h5>
            @elseif($user->status == 'hiatus')
               <h5>On Hiatus until {{ $user->date_hiatus_end->toDateString() }}</h5>
            @elseif($user->status == 'applicant')
               <h5>Applied {{ $user->date_applied->toDateString() }}</h5>
            @endif    

            <p style="margin:15px 0px 10px;">@if($user->status == 'active')<span class="badge badge-success">Active</span>
            @elseif($user->status == 'hiatus')<span class="badge badge-warning">On Hiatus</span>
            @elseif($user->status == 'applicant')<span class="badge badge-warning">Applicant</span>
            @else
            <span class="badge badge-danger">Withdrawn</span>@endif
            @php ($roles = $user->roles()->get())
            @if(count($roles) > 0)
               @foreach($roles as $role)
                  @php($role_name = $role->role()->first())
                  <span class="badge badge-primary">{{ $role_name->name }}</span>&nbsp;
               @endforeach
            @endif
            </p>
            @php ($teams = $user->teams()->get())
            @if(count($teams) > 0)
               <p>
               @foreach($teams->unique() as $team)
               <a href="/teams/{{ $team->id }}" title="View Team Profile"><span class="badge badge-warning badge-team">{{ $team->name }}</span></a>&nbsp;
               @endforeach
            @endif

            <h5 style="margin-top:25px;">
            <a href="mailto:{{ $user->email }}" title="Email"><i class="far fa-envelope fa-2x circle-icon-64"></i></a>

            @php($socials = $user->socials()->get())
            @if(count($socials)>0)
               @foreach($socials as $social)
                  @switch($social->service)
                     @case('twitter')
                        &nbsp;&nbsp;<a href="https://twitter.com/{{ $social->profile }}" title="Twitter"><i class="fab fa-twitter fa-2x circle-icon-64"></i></a>
                        @break
                     @case('instagram')
                        &nbsp;&nbsp;<a href="https://instagram.com/{{ $social->profile }}" title="Instagram"><i class="fab fa-instagram fa-2x circle-icon-64"></i></a>
                        @break
                     @case('facebook')
                        &nbsp;&nbsp;<a href="{{ $social->profile }}" title="Facebook"><i class="fab fa-facebook fa-2x circle-icon-64"></i></a>
                        @break
                     @case('snapchat')
                        &nbsp;&nbsp;<a href="https://snapchat.com/add/{{ $social->profile }}" title="Snapchat"><i class="fab fa-snapchat fa-2x circle-icon-64"></i></a>
                        @break
                     @case('linkedin')
                        &nbsp;&nbsp;<a href="https://www.linkedin.com/in/{{ $social->profile }}" title="LinkedIn"><i class="fab fa-linkedin fa-2x circle-icon-64"></i></a>
                        @break
                  @endswitch
               @endforeach
            @endif
            </h5>
         </div>

         <div class="col-md-2.5">

         @if (($user->id == \Auth::user()->id) || (Auth::user()->can('manage-users')))
            <div class="hovereffect">
               @if ($user->photo != NULL)
                  <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/users/' . $user->photo ?>-512px.jpeg" onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
               @else
                  <img src="/img/no-user-photo.png" style="float:right; max-height:240px;" class="img-circle"/>   
               @endif
               <div class="overlay">
                  <a class="img-upload" href="#" target="popup" onclick="window.open('/image-crop/users/{{ $user->id }}','popup','width=640,height=790'); return false;"><i class="fas fa-file-upload fa-3x"></i></a>
               </div>
            </div>
         @else
            @if ($user->photo != NULL)
               <img class="profile-image img-responsive" style="" src="<?php echo '/storage/images/users/' . $user->photo ?>-512px.jpeg"  onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
            @else
               <img src="/img/no-user-photo.png" style="float:right; max-height:240px;" class="img-circle"/> 
            @endif
         @endif
         </div>
      </div>
      <div class="row">
         <p style="margin:25px 5px 0px;">
            @php ($skills = $user->skills()->get())
            @if(count($skills) > 0)
               @foreach($skills as $skill)
                  <a href="/members/skill/{{ $skill->id }}" title="{{ $skill->skill }}"><span class="badge bg-olive badge-large">{{ $skill->skill }}</span></a>&nbsp;
               @endforeach
            @endif
            </p>
      </div>
   </div>
</div>
