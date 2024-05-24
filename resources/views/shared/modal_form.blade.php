<?php

   /**

      Generates a simple modal with ajax POST backend

      Parameters:
      $name - ID of modal (must be unique on page)
      $url - URL for ajax POST - use {$id} in URL to insert returned recordID
      $method - POST method (POST, PATCH, DELETE, etc) - optional
      $hidden - array of hidden fields [$key => $value] to include with modal form - optional
      $buttons - array of button labels ['yes' = 'OK', 'no' => 'Cancel'] - optional

      slots

      @slot('title') - modal title

      Result Callback functions - optional
      Callbacks run as soon as modal closes

      modal_$name_success(response data) - Run when request returns OK (200)
      modal_$name_error(response data) - Run when request returns Error (4xx, 5xx)


    */

   if (!isset($method)) {
      $method = 'POST';
   }

   if (@!is_array($buttons)) {
      $buttons = [
         'ok' => 'OK',
         'cancel' => 'Cancel'
      ];
   }

?>

{{-- Builds a simple modal with ID and optional hidden fields --}}
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
         <form id="form-{{ $name }}">
         @csrf
            <div class="modal-body">
            {{ $slot }}
               @isset($hidden)
                  @foreach($hidden as $key => $value)
                     <input type="hidden" id="{{ $key }}" name="{{ $key }}" value="{{ $value }}">
                  @endforeach
               @endisset
            </div>
         </form>
         <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-cancel" data-dismiss="modal">{{ $buttons['cancel'] }}</button>
            <button type="button" class="btn btn-primary btn-ok">{{ $buttons['ok'] }}</button>
         </div>
      </div>
   </div>
</div>

@push('js')

<script>

   $('#modal-{{ $name }}').on('show.bs.modal', function(e) {
      var data = $(e.relatedTarget).data();
      $('.title', this).html(data.recordTitle);
      $('.btn-ok', this).data('recordId', data.recordId);
   });

   $('#modal-{{ $name }}').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget)
            var $overlayDiv = $modalDiv.find('.overlay')
            var $overlayIcon = $overlayDiv.find('.overlay-icon')
            var id = $(this).data('recordId')
            var url = '{{ $url }}'

            event.preventDefault();
            $overlayDiv.removeClass('invisible')

            url = url.replace(/{id}/, id)

            $.ajax({
                type:"POST",
                url:url,
                data:$("#form-{{ $name }}").serialize(),
                method: '{{ $method }}',
                headers: {
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                success: function (data, textStatus, oHTTP) {
                  $overlayIcon.removeClass('fa-spin').removeClass('fa-sync')
                  $overlayIcon.addClass('fa-check-circle').addClass('text-success').fadeIn('slow')
                  window.setTimeout(function(){
                     $modalDiv.modal('hide')
                     $overlayDiv.addClass('invisible')
                     $overlayIcon.removeClass('fa-check-circle').removeClass('text-success')
                     $overlayIcon.addClass('fa-spin').addClass('fa-sync')

                     if (typeof modal_{{ $name }}_success === "function") {
                        modal_{{ $name }}_success(data)
                     }
                  }, 1250);
                },
                error: function (data, textStatus, oHTTP) {
                  $overlayIcon.removeClass('fa-spin').removeClass('fa-sync')
                  $overlayIcon.addClass('fa-times-circle').addClass('text-danger').fadeIn('slow')
                  window.setTimeout(function(){
                     $modalDiv.modal('hide')
                     $overlayDiv.addClass('invisible')
                     $overlayIcon.removeClass('fa-times-circle').removeClass('text-danger')
                     $overlayIcon.addClass('fa-spin').addClass('fa-sync')
                     if (typeof modal_{{ $name }}_error === "function") {
                        modal_{{ $name }}_error(data)
                     }
                  }, 1250);
                }
            });
         });

   $.ajaxSetup({
      headers: {
         'Accept': 'application/json',
         'X-CSRF-TOKEN': '{{ csrf_token() }}',
      }
   });

</script>

@endpush
