@extends('kiosk')

@section('title', 'Upload Image')

@section ('customhead')
    <!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.4/croppie.min.css">
<title>Image Upload</title>

@endsection

@section('content')

<div class="container">
<div class="col-md-6" id="upload-form">
	<div class="panel panel-default">
	  <div class="panel-heading"><strong>Upload & Crop Image</strong></div>
	  <div class="panel-body" style="padding-top:0">

	  	<div class="row">
	  		<div class="text-center" style="padding:10px 10px 20px;margin: 0 auto; ">
           <p style="padding-top:0;font-weight:bold;">(Minimum width 1024px for best results)</p>
            <input type="file" id="upload">
	  		</div>
	  	</div>
      <div class="row">
         <div class="col-md-5 text-center">
            <div id='default-image' class='hidden'>{{ asset('/') }}/img/image-upload.png</div>         
            <div id="upload-image" style="width:500px"></div>
         </div>
      </div>
      <div class="row" style="margin-top:25px;">
         <button class="btn btn-success upload-result" disabled="true">Upload Image</button>
      </div>
<?php /*      <div class="row" style="display:none">
         <div class="col-md-5" style="">
            <div id="upload-image-i" style="background:#e1e1e1;width:300px;padding:30px;height:300px;margin-top:30px"></div>
         </div>
      </div> */ ?>

	  </div>
	</div>
</div>
</div>

@endsection

@section('customjs')
   <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.4/croppie.js"></script>

   <script type="text/javascript">
      $.ajaxSetup({
      headers: {
         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
      });

      $uploadCrop = $('#upload-image').croppie({
         url: $("#default-image").html() ,
         disableExif: true,
         viewport: {
            width: 512,
            height: 512,
            type: 'square'
         },
         boundary: {
            width: 512,
            height: 512
         }
      });

      $('#upload').on('change', function () { 
         var reader = new FileReader();
         $('.upload-result').removeAttr('disabled');
         reader.onload = function (e) {
            $uploadCrop.croppie('bind', {
               url: e.target.result
            }).then(function(){
               console.log('jQuery bind complete');
            });
         }
         reader.readAsDataURL(this.files[0]);
      });

      $('.upload-result').on('click', function (ev) {
         $uploadCrop.croppie('result', {
            type: 'canvas',
            size: { width: 1024, height: 1024 },
            format: 'png'
         }).then(function (resp) {
            $.ajax({
               url: "@if($photo_type != NULL)/image-crop/{{ $photo_type }} @else /image-crop @endif ",
               type: "POST",
               data: {"image":resp @if($photo_type != NULL),"photo_type":"{{ $photo_type }}"@endif @if($id != NULL),"id":"{{ $id }}"@endif },
               success: function (data, textStatus, oHTTP) {
                  //alert(data.filename)
                  $('#upload-form').fadeOut('slow', function(here){ 
                     $('#upload-form').replaceWith('<div style="top:50%;"><i class="fas fa-check-circle fa-10x" style="color:green"></i></div>');
                     setTimeout(function(){ window.close(); }, 1000);
                  });

               }
            });
         });

      });

</script>
@endsection

