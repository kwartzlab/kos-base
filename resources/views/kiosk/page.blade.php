@extends('kiosk')

@section ('customhead')
@endsection

@section('content')

<div class="page-icon-wrapper">
   @if(isset($page['icon']))
      @switch($page['icon'])
         @case('success')
            <i class="fas fa-check-circle"></i>
         @break
         @case('error')
            <i class="fas fa-ban"></i>
         @break
         @case('warning')
            <i class="fas fa-exclamation-triangle"></i>
         @break
         @case('info')
            <i class="fas fa-info-circle"></i>
         @break
         @case('cancel')
            <i class="fas fa-times-circle"></i>
         @break
      @endswitch
   @endif
</div>

@if(isset($page['heading']))
  <h1>{{ $page['heading'] }}</h1>
@endif

@if(isset($page['subheading']))
  <h3>{{ $page['subheading'] }}</h3>
@endif

@if(isset($page['text']))
  <p>{{ $page['text'] }}</p>
@endif

@endsection

@section('customjs')
@endsection
