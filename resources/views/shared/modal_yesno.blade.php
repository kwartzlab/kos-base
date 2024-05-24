<?php

   /**

      Generates a simple modal with ajax POST backend

      Parameters:
      $name - ID of modal (must be unique on page)
      $buttons - array of button labels ['yes' = 'OK', 'no' => 'Cancel'] - optional

      slots

      @slot('title') - modal title

      Result Callback functions - optional
      Callbacks run as soon as modal closes

      modal_$name_yes(response data) - Run when user presses Yes button
      modal_$name_no(response data) - Run when user presses No button

    */

   if (!isset($method)) {
      $method = 'POST';
   }

   if (@!is_array($buttons)) {
      $buttons = [
         'yes' => 'Yes',
         'no' => 'No'
      ];
   }

?>

<div class="modal fade" id="modal-{{ $name }}" tabindex="-1" role="dialog" aria-labelledby="modal-label-{{ $name }}" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="overlay d-flex justify-content-center align-items-center invisible">
            <i class="overlay-icon fas fa-8x fa-spin fa-sync text-secondary"></i>
         </div>
         <div class="modal-header">
            <h4 class="modal-title" id="modal-label-{{ $name }}">{{ $title }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>
         <div class="modal-body">
            {{ $slot }}
         </div>
         <div class="modal-footer">
            @if($buttons['no'] != NULL)
               <button type="button" class="btn btn-danger btn-no">{{ $buttons['no'] }}</button>
            @endif
            @if($buttons['yes'] != NULL)
               <button type="button" class="btn btn-success btn-yes">{{ $buttons['yes'] }}</button>
            @endif
         </div>
      </div>
   </div>
</div>

@push('js')

<script>

   $('#modal-{{ $name }}').on('show.bs.modal', function(e) {
      // if there was a related target, get record ID, otherwise it needs to be assigned manually before modal is opened
      var data = $(e.relatedTarget).data();
      if (typeof data != "undefined") {
         $('.btn-yes', this).data('recordId', data.recordId);
         $('.btn-no', this).data('recordId', data.recordId);
      }
   });

   @if($buttons['yes'] != NULL)
   $('#modal-{{ $name }}').on('click', '.btn-yes', function(e) {
      var $modalDiv = $(e.delegateTarget)
      var id = $(this).data('recordId')
      $modalDiv.modal('hide')
      if (typeof modal_{{ $name }}_yes === "function") {
         modal_{{ $name }}_yes($(this).data())
      }
   });
   @endif


   @if($buttons['no'] != NULL)
   $('#modal-{{ $name }}').on('click', '.btn-no', function(e) {
      var $modalDiv = $(e.delegateTarget)
      var id = $(this).data('recordId')
      $modalDiv.modal('hide')
      if (typeof modal_{{ $name }}_no === "function") {
         modal_{{ $name }}_no($(this).data())
      }
   });
   @endif

</script>

@endpush
