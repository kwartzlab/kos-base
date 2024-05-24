@extends('adminlte::page')

@section('title', $form->name)

@section('content_header')
    <h1>{{ $form->name }} </h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
   <form method="POST" action="/forms/{{ $form->id }}/save" enctype="multipart/form-data">
   {{ csrf_field() }}
   <input type="hidden" id="form_id" name="form_id" value="{{ $form->id }}">
   <input type="hidden" id="returning_user" name="returning_user" value="0">

      <div class="card-body">
         @if($form->description != NULL)
            {!! $form->description !!}
            <hr>
         @endif

         @foreach($form_fields as $field_uuid => $form_field)

            @if($form_field->label != NULL)
               <?php
                  if ($form_field->name == NULL) {
                     $element_name = 'element-' . $field_uuid;
                     $element_value = old('element-' . $field_uuid);
                  } else {
                     $element_name = 'element-' . $form_field->name;
                     $element_value = old('element-' . $form_field->name);
                  }

               ?>
               <div class="row">
                  @switch($form_field->type)
                     @case('input')
                        <div class="form-group col-md-6">
                           <label for="{{ $element_name }}">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                           <input type="text" class="form-control @if($errors->has($element_name)) is-invalid @endif" name="{{ $element_name }}" id="{{ $element_name }}" value="{{ $element_value }}" @if($form_field->length != NULL) maxlength="{{ $form_field->length }}" @endif @if($form_field->required) required @endif>
                        </div>
                        @break
                     @case('text')
                        <div class="form-group col-md-12" id="{{ $element_name }}">
                           {!! $form_field->label !!}
                        </div>
                        @break
                     @case('textarea')
                        <div class="form-group col-md-6">
                           <label for="{{ $element_name }}">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                           <textarea class="form-control @if($errors->has($element_name)) is-invalid @endif" rows="3" role="textbox" name="{{ $element_name }}" id="{{ $element_name }}" @if($form_field->length != NULL) maxlength="{{ $form_field->length }}" @endif aria-multiline="true" @if($form_field->required) required @endif>{{ $element_value }}</textarea>
                        </div>
                        @break
                     @case('switch')
                        <div class="row col-md-12">
                           <div style="margin-bottom:0px">
                              <label class="col-md-12">{!! $form_field->label !!} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                           </div>
                        </div>
                        <div class="form-group col-md-12" style="margin:5px 10px 5px 0px">
                           <div style="display:inline-block;position:relative;bottom:15px">{{ $form_field->off }}</div>
                           <label class="switch">
                              <input type="checkbox" class="primary" id="{{ $element_name }}" name="{{ $element_name }}" @if ($element_value != NULL) checked @endif @if($form_field->required) required @endif>
                              <span class="slider round"></span>
                           </label>
                           <div style="display:inline-block;position:relative;bottom:15px">{{ $form_field->on }}</div>
                        </div>
                        @break
                     @case('dropdown')
                        <div class="form-group col-md-4">
                        <label for="{{ $element_name }}">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                           <select class="form-control" name="{{ $element_name }}" id="{{ $element_name }}">
                              @foreach($form_field->options as $field_uuid => $field_option)
                                 <pre><?php print_r($field_option->value); ?></pre>
                                 <pre><?php print_r($element_value); ?></pre>
                                 <option value="@if($field_option->value == NULL){{ $field_option->name }}@else{{ $field_option->value }}@endif" @if ($element_value == $field_option->value) selected="selected" @endif>{{ $field_option->name }}</option>
                              @endforeach
                           </select>
                        </div>
                        @break
                     @case('radio')
                        <div class="form-group col-md-8">
                           <label class="@if($errors->has($element_name)) is-invalid @endif">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                              @foreach($form_field->options as $field_uuid => $field_option)
                                 <div class="form-check" style="margin-left:10px;">
                                    <?php
                                       if ($field_option->value == NULL) { $field_value = $field_option->name; } else { $field_value = $field_option->value; }
                                    ?>
                                    <input type="radio" class="form-check-input" name="{{ $element_name }}" id="{{ $element_name }}[{{ $loop->iteration }}]" value="{{ $field_value }}" @if($element_value == $field_option->value) checked @endif @if($form_field->required) required @endif>
                                    <label for="{{ $element_name }}[{{ $loop->iteration }}]" class="form-check-label">{{ $field_option->name }}</label>
                                 </div>
                              @endforeach
                        </div>
                     @break
                     @case('checkbox')
                        <div class="form-group col-md-8">
                           <label class="@if($errors->has($element_name)) is-invalid @endif">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                              @foreach($form_field->options as $field_uuid => $field_option)
                                 <div class="form-check" style="margin-left:10px;">
                                    <?php
                                       if ($field_option->value == NULL) { $field_value = $field_option->name; } else { $field_value = $field_option->value; }
                                    ?>
                                    <input type="checkbox" class="form-check-input" name="{{ $element_name }}[{{ $loop->iteration }}]" id="{{ $element_name }}-{{ $loop->iteration }}" value="{{ $field_value }}" @if( is_array($element_value) && in_array($field_value, $element_value)) checked @endif @if($form_field->required) required @endif>
                                    <label for="{{ $element_name }}[{{ $loop->iteration }}]" class="form-check-label">{{ $field_option->name }}</label>
                                 </div>
                              @endforeach
                        </div>
                        @break
                     @case('upload')
                        <div class="form-group col-md-6">
                           <label for="{{ $element_name }}">{{ $form_field->label }} @if($form_field->required) <span class="text-danger">*</span> @endif</label>
                           <div class="input-group">
                              <div class="input-group-prepend">
                                 <div class="input-group-text"><i class="fas fa-file-upload"></i></div>
                              </div>
                              <div class="custom-file">
                                 <input type="file" class="custom-file-input" @if($form_field->multiupload) multiple @endif name="{{ $element_name }}" id="{{ $element_name }}" />
                                 <label class="custom-file-label" for="customFile">@if($form_field->multiupload) Choose file(s) @else Choose file @endif</label>
                              </div>
                           </div>
                        </div>
                     @break
                     @case('hidden')
                        <input type="hidden" id="{{ $element_name }}" name="{{ $element_name }}" value="{{ $form_field->default }}">
                        @break
                  @endswitch
               </div>
            @endif
         @endforeach

         @if($form->special_form != NULL)
            @includeIf('forms.special.' . $form->special_form)
         @endif

      </div>
      <div class="card-footer">
         <button type="submit" id="btnsubmit" class="btn btn-primary">
         @if($form->submit_label != NULL)
            {{ $form->submit_label }}
         @else Send Form
         @endif
         </button>
      </div>

   </form>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
    <link rel="stylesheet" href="/css/summernote-bs4.css">
@stop

@section('js')
<script src="/js/jquery.inputmask.bundle.min.js"></script>
<script src="/js/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script>
$(document).ready(function(){

   bsCustomFileInput.init()

   document.querySelector('#btnsubmit').onclick = function () {
      // make sure we have all the required fields filled out before disabling the button
      var allRequired = true;
      var required = $('input,textarea,select').filter('[required]:visible');
      required.each(function(){
         if($(this).val() == ''){
            allRequired = false;
         }
      });

      // validate checkboxes
      var required = $('input:checkbox').filter('[required]');
      required.each(function(){
         if($(this).prop("checked") == false){
            allRequired = false;
         }
      });

      if(allRequired){
         $("#btnsubmit").addClass("disabled");
         $("#btnsubmit").html('<i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp;Sending');
      }
   }

   @forelse($errors->messages() as $element_name => $message)
      $("#{{ $element_name }}").change(function () {
        $("#{{ $element_name }}").removeClass('is-invalid');
      });
   @empty
   @endforelse

   @foreach($form_fields as $field_uuid => $form_field)
      @if($form_field->label != NULL)
         <?php
            if ($form_field->name == NULL) {
               $element_id = 'element-' . $field_uuid;
            } else {
               $element_id = 'element-' . $form_field->name;
            }
         ?>
         @switch($form_field->type)
            @case('input')
               @switch($form_field->mask)
                  @case('date')
                     $("#{{ $element_id }}").inputmask("9999-99-99");
                     @break
                  @case('phone')
                     $("#{{ $element_id }}").inputmask("(999) 999-9999");
                     @break
                  @case('postal')
                     $("#{{ $element_id }}").inputmask("A9A 9A9");
                     @break
               @endswitch
               @break
            @case('textarea')
               @if($form_field->usehtml)
                  $('#{{ $element_id }}').summernote({
                     height:150,
                  });
               @endif
               @break
            @case('switch')

               @break
            @case('dropdown')

               @break
            @case('radio')

               @break
            @case('checkbox')

               @break
         @endswitch
      @endif
    @endforeach

    $.ajaxSetup({
            headers: {
               'Accept': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
         });
});
</script>
@stop
