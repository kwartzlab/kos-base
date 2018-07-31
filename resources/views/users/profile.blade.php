<div class="box box-warning member-profile">
    <div class="box-body">

    
    @if ($user->photo != NULL)
        <img src="<?php echo '/storage/photos/' . $user->photo ?>" style="float:right; max-height:240px;" class="img-circle"/>
    @else
        <img src="/img/0.png" style="float:right; max-height:240px;" class="img-circle"/>
    @endif

    <h1>{{ $user->first_name }} {{ $user->last_name }}</h1>

    @if($user->status == 'active')
        <h4>Member Since {{ $user->date_admitted }}</h4>
    @elseif($user->status == 'inactive')
        <h4>Withdrawn {{ $user->date_withdrawn }}</h4>
    @elseif($user->status == 'hiatus')
        <h4>On Hiatus until {{ $user->date_hiatus_end }}</h4>
    @elseif($user->status == 'applicant')
        <h4>Applied {{ $user->date_applied }}</h4>
    @endif    

    <p>@if($user->status == 'active')<span class="label label-success">Active</span>
    @elseif($user->status == 'hiatus')<span class="label label-warning">On Hiatus</span>
    @elseif($user->status == 'applicant')<span class="label label-warning">Applicant</span>
    @else
    <span class="label label-danger">Withdrawn</span>@endif
    @if($user->is_admin()) <span class="label label-primary">Admin</span>@endif
    @if($user->is_keyadmin()) <span class="label label-primary">Key Admin</span>@endif
    </p>

    <ul class="contact-info">
    <li><i class="fa fa-envelope-o"></i> <a href="mailto:{{ $user->email }}">{{ $user->email }}</a></li>
    </ul>

    <?php $trainer_tools = $user->trainer_for(); ?>
    @if ($trainer_tools != NULL)
        <p style="margin-top:25px;">Trainer For: 
        @foreach($trainer_tools as $tool)
            <span class="label label-warning"> {{ \App\Gatekeeper::where('id',$tool->gatekeeper_id)->value('name') }}</span>
        @endforeach

        </p>
    @endif


    </div>
</div>
