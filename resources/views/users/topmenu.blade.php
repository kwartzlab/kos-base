<?php /* 

   User profile menu & Notifications - top right
   Included in vendor/page.blade.php to replace default logout link 
   (must be re-added if vendor assets are updated and replaced)
   
*/?>
            <!-- Notifications Panel -->
            <li class="nav-item dropdown" id="notifications">
              <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge badge-danger navbar-badge">15</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="fas fa-envelope mr-2"></i> 4 new messages
                  <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="fas fa-users mr-2"></i> 8 friend requests
                  <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="fas fa-file mr-2"></i> 3 new reports
                  <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
              </div>
            </li>

            <!-- User Account Menu -->
            <li class="nav-item dropdown user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                @if (\Auth::user()->photo != NULL)
                    <img src="/storage/images/users/{{ Auth::user()->photo }}-256px.jpeg" class="user-image" alt="Member Image" onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';"/>
                @else
                  <img src="/storage/images/users/no_profile_photo.png" class="user-image" alt="Member Image"/>
                @endif
                {{ \Auth::user()->get_name() }}
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- The user image in the menu -->
                <li class="user-header" style="background-color:#367fa9;color:#fff;">
                  
                  @if (\Auth::user()->photo != NULL)
                    <img src="/storage/images/users/{{ Auth::user()->photo }}-256px.jpeg" class="img-circle" alt="Member Image"/>
                  @else
                    <img src="/storage/images/users/no_profile_photo.png" class="img-circle elevation-2" alt="Member Image"/>
                  @endif
                  <p class="text-lg"> {{ \Auth::user()->first_name }} {{ \Auth::user()->last_name }}<br /><small>Joined {{ \Auth::user()->date_admitted->format('Y-m-d') }}</small></p>
                </li>
                <li class="user-body text-center">
                     @if(\Auth::user()->status == 'active')<span class="badge badge-success">Active</span>
                     @elseif(\Auth::user()->status == 'hiatus')<span class="badge badge-warning">On Hiatus</span>
                     @elseif(\Auth::user()->status == 'applicant')<span class="badge badge-warning">Applicant</span>
                     @else
                     <span class="badge badge-danger">Withdrawn</span>@endif
                     &nbsp;
                     @php ($roles = \Auth::user()->roles()->get())
                     @if(count($roles) > 0)
                        @foreach($roles as $role)
                           @php($role_name = $role->role()->first())
                           <span class="badge badge-primary">{{ $role_name->name }}</span>&nbsp;
                        @endforeach
                     @endif
                </li>

                <li class="user-footer">
                  <a href="/members/{{ \Auth::user()->id }}/profile" class="btn btn-primary btn-flat">My Profile</a>
                  <div class="float-right">
                    <form method="POST" action="/logout">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary btn-flat">Logout</button>
                    </form>
                  </div>
                </li>
              </ul>
            </li>
