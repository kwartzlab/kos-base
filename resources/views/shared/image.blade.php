@extends('kiosk')

@section('title', 'Upload Image')

@section ('customhead')
    <!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/imgareaselect-animated.css') }}">
<title>Image Upload</title>

@endsection

@section('content')

<div class="container">
      <form action="{{ url('image') }}" method="post" enctype="multipart/form-data">
         <div class="form-group">
               <label for="inputimage"><h3>Image Uploader</h3></label><br />
               <input type="file" name="profile_image" id="inputimage" class="image" required>
               <input type="hidden" name="x1" value="" />
               <input type="hidden" name="y1" value="" />
               <input type="hidden" name="w" value="" />
               <input type="hidden" name="h" value="" />
               <input type="hidden" name="scaled_w" value="" />
               <input type="hidden" name="scaled_h" value="" />
         </div>
         {{ csrf_field() }}
         <button type="submit" class="btn btn-primary">Submit</button>
      </form>
      <div class="row mt-5">
         <p ><img id="previewimage" style="display:none;"/></p>
         @if(session('path'))
               <img src="{{ session('path') }}" />
         @endif
      </div>
</div>

@endsection

@section('customjs')
   <script src="{{ asset('js/jquery.imgareaselect.pack.js') }}"></script>
      <script>
         jQuery(function($) {
   
               var p = $("#previewimage");
               $("body").on("change", ".image", function(){
   
                  var imageReader = new FileReader();
                  imageReader.readAsDataURL(document.querySelector(".image").files[0]);
   
                  imageReader.onload = function (oFREvent) {
                     p.attr('src', oFREvent.target.result).fadeIn();
                     img_width = $('.image').css("width")
                     img_height = $('.image').css("height")
                     img_scale = $('.image').css("")
                  };

               });

               $('#previewimage').imgAreaSelect({
                  aspectRatio: '1:1',
                  handles: 'corners',
                  minWidth: '256',
                  minHeight: '256',
                  imageHeight: '1080',
                  imageWidth: '1920',
                  show: true,
                  onSelectEnd: function (img, selection) {
                     $('.image').css('transform','scale(1.0)');

                     $('input[name="x1"]').val(selection.x1);
                     $('input[name="y1"]').val(selection.y1);
                     $('input[name="w"]').val(selection.width);
                     $('input[name="h"]').val(selection.height);
                     $('input[name="scale"]').val(img_scale);
                     $('input[name="scaled_w"]').val(img_width);
                     $('input[name="scaled_h"]').val(img_height);
                  }
               });
         });
      </script>
@endsection
