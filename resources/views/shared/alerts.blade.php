@if (session('success'))
<div class="alert alert-success">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	
	<h5 style="margin-bottom:0;"><i class="far fa-check-circle"></i>&nbsp;&nbsp;{{ session()->get('success') }}</h5>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	
	<h5 style="margin-bottom:0;"><i class="fas fa-ban"></i>&nbsp;&nbsp;{{ session()->get('error') }}</h5>
</div>
@endif

@if (session('info'))
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	
	<h5 style="margin-bottom:0;"><i class="fas fa-info-circle"></i>&nbsp;&nbsp;{{ session()->get('info') }}</h5>
</div>
@endif

@if (session('warning'))
<div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	
	<h5 style="margin-bottom:0;"><i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;{{ session()->get('warning') }}</h5>
</div>
@endif


@if (count($errors))

<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>	
	<h5 style="margin-bottom:0;"><i class="fas fa-ban"></i>&nbsp;&nbsp;Please correct the following form errors:</h5>
	<ul style="margin-top:15px;font-size:110%">
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>

@endif