@extends('kiosk')

@section ('customhead')
@endsection

@section('content')

<div class="page-icon-small">
   @if(isset($page['icon']))
      @switch($page['icon'])
         @case('question')
            <i class="fas fa-question-circle"></i>
         @break
         @case('warning')
            <i class="fas fa-exclamation-triangle"></i>
         @break
         @case('info')
            <i class="fas fa-info-circle"></i>
         @break
      @endswitch
   @endif
</div>

@if(isset($page['heading'])) 
  <h2 style="margin-bottom:30px">{!! $page['heading'] !!}</h2>
@endif

@if(isset($page['subheading'])) 
  <h1 style="margin-bottom:30px">{!! $page['subheading'] !!}</h1>
@endif

@if(isset($page['text'])) 
  <h2>{!! $page['text'] !!}</h2>
@endif

<form method="POST" action="{{ $page['form_url'] }}">
   {{ csrf_field() }}
   @if (isset($page['form_hidden']))
      @foreach($page['form_hidden'] as $key => $value)
         <input type="hidden" name="{{ $key }}" value="{{ $value }}">
      @endforeach
   @endif
   <div class="row" style="margin-top:50px;">
      <div class="col-xs-4 col-xs-offset-2">
         <button type="submit" name="no" class="btn btn-danger btn-lg btn-block">No</button>
      </div>
      <div class="col-xs-4">
         <button type="submit" name="yes" class="btn btn-success btn-lg btn-block">Yes</button>
      </div>
   </div>
</form>


@endsection

