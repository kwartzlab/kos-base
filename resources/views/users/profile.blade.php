<div class="card card-warning card-outline member-profile">
   <div class="card-body">

      <div class="row">
         <div class="col-md-9">
            <h2>{{ $user->first_name }} {{ $user->last_name }}</h2>

            @if($user->status == 'active')
               <h5>Member Since {{ $user->date_admitted }}</h5>
            @elseif($user->status == 'inactive')
               <h5>Withdrawn {{ $user->date_withdrawn }}</h5>
            @elseif($user->status == 'hiatus')
               <h5>On Hiatus until {{ $user->date_hiatus_end }}</h5>
            @elseif($user->status == 'applicant')
               <h5>Applied {{ $user->date_applied }}</h5>
            @endif    

            <p>@if($user->status == 'active')<span class="badge badge-success">Active</span>
            @elseif($user->status == 'hiatus')<span class="badge badge-warning">On Hiatus</span>
            @elseif($user->status == 'applicant')<span class="badge badge-warning">Applicant</span>
            @else
            <span class="badge badge-danger">Withdrawn</span>@endif
            @if($user->is_admin()) <span class="badge badge-primary">Admin</span>@endif
            @if($user->is_keyadmin()) <span class="badge badge-primary">Key Admin</span>@endif
            </p>

            <h5 style="margin-top:25px;"><i class="far fa-envelope"></i> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></h5>

            <?php $trainer_tools = $user->trainer_for(); ?>
            @if ($trainer_tools != NULL)
               <p style="margin-top:25px;">Trainer For: 
               @foreach($trainer_tools as $tool)
                  <span class="badge badge-warning" style="margin-left:8px;"> {{ \App\Gatekeeper::where('id',$tool->gatekeeper_id)->value('name') }}</span>
               @endforeach
               </p>
            @endif
         </div>

         <div class="col-md-2.5">

         @if ($user->photo != NULL)

               <div class="hovereffect">
                  <img class="profile-image img-responsive" style="" src="<?php echo '/storage/photos/' . $user->photo ?>" alt="">
                  <div class="overlay">
                     <a class="img-upload" href="#"><i class="fas fa-file-upload fa-3x"></i></a>
                  </div>
               </div>

         @else
               <img src="/img/0.png" style="float:right; max-height:240px;" class="img-circle"/>
         @endif

         </div>
      </div>
   </div>
</div>
