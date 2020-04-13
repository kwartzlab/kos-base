@extends('kiosk')

@section ('customhead')
@endsection

@section ('navbar')
<nav class="navbar navbar-default navbar-fixed-top" style="background-color:#EEE;">
   <div class="container" style="text-align:center;margin-top:5px;">
      <div class="col-xs-3" style="text-align:left;padding:0;">
         <button id="sort-alpha" class="btn btn-warning btn-navbar">Alphabetically</button>
      </div>
      <div class="col-xs-6">
         <h3 style="margin-top:10px">Select User to assign</h3>
      </div>
      <div class="col-xs-3" style="text-align:right;padding:0;">
         <button id="sort-latest" class="btn btn-warning btn-navbar">Latest Users</button>
      </div>
   </div>
</nav>
@endsection

@section('content')

<form method="POST" action="/kiosk/create_key">
{{ csrf_field() }}
@if (isset($page['form_hidden']))
   @foreach($page['form_hidden'] as $key => $value)
      <input type="hidden" name="{{ $key }}" value="{{ $value }}">
   @endforeach
@endif
<div class="row userlist">
   @foreach ($users as $user)
   <div class="col-xs-6" data-sname="{{ $user->get_name('first') }}" data-sid={{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}>
      <button type="submit" name="user_id" class="btn btn-primary btn-block btn-user" value="{{ $user->id }}">{{ $user->get_name() }}</button>
   </div>
   @endforeach
</div>
</form>

@endsection

@section('customjs')
<script>
   $(document).ready(function(){

      $.fn.sortChildren = function (sortingFunction) {
         return this.each(function () {
            const children = $(this).children().get();
            children.sort(sortingFunction);
            $(this).append(children);
         });
      };

      document.querySelector('#sort-latest').onclick = function () {
         $(".userlist").sortChildren((a, b) => b.dataset.sid > a.dataset.sid ? 1 : -1);
      }      

      document.querySelector('#sort-alpha').onclick = function () {
         $(".userlist").sortChildren((a, b) => a.dataset.sname > b.dataset.sname ? 1 : -1);
      }      

   });
</script>

@endsection