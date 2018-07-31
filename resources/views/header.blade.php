  <!-- Main Header -->
  <header class="main-header">

    <!-- Logo -->
    <a href="/" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b><i class="fa fa-database"></i></b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><i class="fa fa-database"></i> <b>kOS</b></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          @auth

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                @if (\Auth::user()->photo != NULL)
                    <img src="/storage/photos/{{ Auth::user()->photo }}" class="user-image" alt="Member Image"/>
                @else
                  <img src="/img/0.png" class="user-image" alt="Member Image"/>
                @endif

                {{ \Auth::user()->first_name }} {{ \Auth::user()->last_name }}
              </a>
              <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header">
                  
                  @if (\Auth::user()->photo != NULL)
                    <img src="/storage/photos/{{ Auth::user()->photo }}" class="img-circle" alt="Member Image"/>
                  @else
                    <img src="/img/0.png" class="img-circle" alt="Member Image"/>
                  @endif

                  <p> {{ \Auth::user()->first_name }} {{ \Auth::user()->last_name }}<br /><small>Joined {{ \Auth::user()->date_admitted }}</small></p>
                  @if(\Auth::user()->status == 'active')<span class="label label-success">Active</span>
                  @elseif(\Auth::user()->status == 'hiatus')<span class="label label-warning">On Hiatus</span>
                  @elseif(\Auth::user()->status == 'applicant')<span class="label label-warning">Applicant</span>
                  @else
                  <span class="label label-danger">Withdrawn</span>@endif
                  @if(\Auth::user()->acl == 'admin') <span class="label label-primary">Admin</span>@endif
                  @if(\Auth::user()->acl == 'keyadmin') <span class="label label-primary">Key Admin</span>@endif
                  </p>
                </li>
                <!-- Menu Body -->
<?php /*                <li class="user-body">
                  <div class="row">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </div>
                  <!-- /.row -->
                </li>
*/ ?>

                <!-- Menu Footer-->
                <li class="user-footer">
                  <div class="pull-left">
                  <a href="/members/{{ \Auth::user()->id }}/profile" class="btn btn-default btn-flat">My Profile</a>
                  </div>
                  <div class="pull-right">
                    <form method="POST" action="/logout">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-default btn-flat">Logout</button>
                    </form>
                  </div>
                </li>
              </ul>
            </li>


          @endauth


          @guest
         
          <li><a href="/login">Login</a></li>
          
          @endguest

        </ul>
      </div>
    </nav>
  </header>
