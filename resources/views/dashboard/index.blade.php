@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Welcome {{ \Auth::user()->get_name('first') }}!</h1>
@stop

@section('content')
@include('shared.alerts')

<?php $user = \App\Models\User::where('id', \Auth::user()->id)->first(); ?>
@if ($user->flags->contains('flag', 'keys_disabled'))
<div class="alert alert-warning">
   <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
   <h5 style="margin-bottom:0;font-weight:bold;"><i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Notice: Your key(s) are currently disabled.</h5>
</div>
@endif

<div class="row">
  <section class="col-md-9">
      {{-- Lab Activity Graph --}}
         <div class="card">
          <div class="card-header border-0">
            <h3 class="card-title small">Lab Activity (last 30 days)</h3>
            <div class="card-tools">
            </div>
          </div>
          <div class="card-body">
            <div class="line-chart" id="lab-activity-chart" style="height:300px;max-height:300px;">
                {!! $lab_activity_chart->container() !!}
            </div>
          </div>
        </div>

        {{-- Latest Members --}}
        <div class="card">
            <div class="card-header border-0">
              <h3 class="card-title small">Welcome New Members</h3>
              <div class="card-tools">
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <ul class="users-list clearfix">
                  @foreach($latest_members as $member)
                      <li style="width:20%">
                            <img class="profile-image img-responsive" style="margin-bottom:10px;max-height:96px !important;" src="{{ asset('/storage/images/users/' . $member->photo) }}-128px.jpeg"  onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
                            <a class="users-list-name text-sm" href="/members/{{ $member->id }}/profile" TITLE="View {{ $member->get_name() }}">{{ $member->get_name() }}</a>
                        <span class="users-list-date text-sm">{{ $member->date_admitted->diffForHumans() }}</span>
                      </li>
                  @endforeach
              </ul>
              <!-- /.users-list -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer text-center" style="display:none;">
              <a href="javascript::">View All Users</a>
            </div>
            <!-- /.card-footer -->
        </div>
  {{-- Latest Applicants --}}
        <div class="card">
            <div class="card-header border-0">
              <h3 class="card-title small">Latest Applicants</h3>
              <div class="card-tools">
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <ul class="users-list clearfix">
                  @foreach($latest_applicants as $member)
                      <li style="width:20%">
                            <img class="profile-image img-responsive" style="margin-bottom:10px;max-height:96px !important;" src="{{ asset('/storage/images/users/' . $member->photo) }}-128px.jpeg"  onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
                            <a class="users-list-name text-sm" href="/members/{{ $member->id }}/profile" TITLE="View {{ $member->get_name() }}">{{ $member->get_name() }}</a>
                        <span class="users-list-date text-sm">{{ $member->date_applied>diffForHumans() }}</span>
                      </li>
                  @endforeach
              </ul>
              <!-- /.users-list -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer text-center" style="display:none;">
              <a href="javascript::">View All Users</a>
            </div>
            <!-- /.card-footer -->
        </div>
  </section>
  <section class="col-md-3">
      {{-- Tool Status --}}
      <div class="card">
          <div class="card-header border-0">
            <h3 class="card-title small">Tool Status</h3>
            <div class="card-tools">
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <ul class="users-list clearfix">

            <table class="table table-striped">
              <tbody>

                @foreach($gatekeepers as $gatekeeper)
                    @if ($gatekeeper->is_authorized())
                      <tr>
                        <td style="padding-left:10px;width:100%;padding-right:0"><a href="/gatekeepers/{{ $gatekeeper->id }}" title="View Profile">{{ $gatekeeper->name }}</a></td>
                        <td class="text-right" style="padding-left:10px; padding-right:10px" nowrap>
                            @php($status = $gatekeeper->current_status()->first())
                            @if($status != NULL)
                                @switch($status->status)
                                  @case('online')
                                      <span class="badge badge-success">Available</span>
                                  @break
                                  @case('offline')
                                      <span class="badge badge-danger">Offline</span>
                                  @break
                                  @case('inuse')
                                      <span class="badge badge-info">In use</span>
                                  @break
                                @endswitch
                            @else
                                <span class="badge badge-warning">Unknown</span>
                            @endif
                        </td>
                      </tr>
                    @endif

                @endforeach

              </tbody>
            </table>
            </ul>

            <!-- /.users-list -->
          </div>
          <!-- /.card-body -->
          <div class="card-footer text-center" style="display:none;">
            <a href="javascript::">View All Users</a>
          </div>
          <!-- /.card-footer -->
      </div>

      {{-- Events Calendar --}}
      <div class="card">
          <div class="card-header border-0">
            <h3 class="card-title small">Upcoming Events</h3>
            <div class="card-tools">
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <ul class="users-list clearfix">

            <table class="table table-striped widget-event">
              <tbody>
                @foreach($events as $key => $daily_events)
                      <tr>
                        <td>
                            <span class="text-muted text-bold">{{ \Carbon\Carbon::CreateFromFormat('Y-m-d',$key)->format('F j') }} @if(\Carbon\Carbon::CreateFromFormat('Y-m-d',$key)->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d'))<span class="text-sm"> - today! @endif</span></span>
                            <ul>
                              @foreach($daily_events as $event)
                                <li><a href="#" data-toggle="tooltip" title="{{ $event->description }}">{{ $event->summary }}</a><br/><span class="text-sm">@if($event->startDateTime == NULL)All Day @else {{ $event->startDateTime->format('g:ia') }} - {{ $event->endDateTime->format('g:ia') }} @endif</span></li>
                              @endforeach
                            </ul>
                        </td>
                      </tr>
                @endforeach

              </tbody>
            </table>
            </ul>

            <!-- /.users-list -->
          </div>
          <!-- /.card-body -->
          <div class="card-footer text-center" style="display:none;">
            <a href="javascript::">View All Users</a>
          </div>
          <!-- /.card-footer -->
      </div>


  </section>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('plugins.Chartjs', true)
@section('js')
  {!! $lab_activity_chart->script() !!}
  <script>
    $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip({
        animation:true,
        html:true,
        trigger:'click hover'
      });
    });
  </script>
@stop
