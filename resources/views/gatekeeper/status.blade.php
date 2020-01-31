

@if($status != NULL)
   @if($gatekeeper->status == 'enabled')
      @switch($status->status)
         @case('online')
            <div class="info-box bg-success">
               <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
               <div class="info-box-content">
                  <span class="info-box-text">Status</span>
                  <span class="info-box-number">Online @if($gatekeeper->type == 'lockout')- Available @endif</span>
               </div>
            </div>
         @break
         @case('offline')
            <div class="info-box bg-danger">
               <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
               <div class="info-box-content">
                  <span class="info-box-text">Status</span>
                  <span class="info-box-number">Offline @if($status->status_text != NULL) - {{ $status->status_text }} @endif</span>
               </div>
            </div>
         @break
         @case('inuse')
            <div class="info-box bg-info">
               <span class="info-box-icon text-white"><i class="fas fa-user"></i></span>
               <div class="info-box-content text-white">
                  <span class="info-box-text">Status</span>
                  <span class="info-box-number">In use by {{ $status->user()->first()->get_name() }} for {{ str_replace(' ago', '', $status->lock_in->diffForHumans()) }}</span>
               </div>
            </div>
         @break
      @endswitch
   @else
      <div class="info-box bg-danger">
         <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
         <div class="info-box-content">
            <span class="info-box-text">Status</span>
            <span class="info-box-number">Offline</span>
         </div>
      </div>
   @endif
@else
   <div class="info-box bg-warning">
      <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
      <div class="info-box-content">
         <span class="info-box-text">Status</span>
         <span class="info-box-number">Unknown</span>
      </div>
   </div>
@endif