<?php
   if (!isset($user_status)) {
      $user_status = config('kwartzlabos.user_status');
   }
?>
@switch($user->status)
   @case('active')
      <span class="badge badge-{{ $user_status[$user->status]['colour'] }}">Active</span>
   @break
   @case('applicant')
      <span class="badge badge-{{ $user_status[$user->status]['colour'] }}">Applicant</span>
   @break
   @case('terminated')
      <span class="badge badge-{{ $user_status[$user->status]['colour'] }}">Withdrawn</span>
   @break
   @case('abandoned')
      <span class="badge badge-{{ $user_status[$user->status]['colour'] }}">Withdrawn</span>
   @break
   @default
   <span class="badge badge-{{ $user_status[$user->status]['colour'] }}">{{ $user_status[$user->status]['name'] }}</span>
@endswitch
