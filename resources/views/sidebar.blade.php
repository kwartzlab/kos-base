  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
        <li class="header">NAVIGATION</li>
        <!-- Optionally, you can add icons to the links -->

<?php

  $current_route = Route::currentRouteName();

  if ($current_route != NULL) {
      $route_array = explode('.',$current_route);
      $controller = $route_array[0];
  } else {
    $controller = NULL;
  }

?>
        <li @if($controller==NULL)class="active"@endif><a href="/"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

        <li @if($current_route=="members.index")class="active"@endif><a href="/members"><i class="fa fa-user"></i> <span>Member Directory</span></a></li>
        <li @if($current_route=="users.create")class="active"@endif><a href="/users/create"><i class="fa fa-edit"></i> <span>Membership Application</span></a></li>

        <li class="header">ADMINISTRATION</li>

        @if((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) 
          <li @if($controller=="users" && $current_route!="users.create")class="active"@endif><a href="/users"><i class="fa fa-user"></i> <span>Member Register</span></a></li>
        @endif
        <li @if($controller=="training")class="active"@endif><a href="/training"><i class="fa fa-mortar-board"></i> <span>Trainers</span></a></li>
        @if(\Auth::user()->acl == 'admin') 
        <li @if($controller=="gatekeepers")class="active"@endif><a href="/gatekeepers"><i class="fa fa-expeditedssl"></i> <span>Gatekeepers</span></a></li>
<?php /*        <li @if($controller=="forms")class="active"@endif><a href="/forms"><i class="fa fa-edit"></i> <span>Web Forms</span></a></li> */ ?>

        <li @if($controller=="reports")class="active"@endif><a href="/reports"><i class="fa fa-file-text-o"></i> <span>Reports</span></a></li>
        @endif



      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>
