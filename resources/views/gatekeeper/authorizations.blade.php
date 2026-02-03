<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Authorizations</h3>
        <div class="card-tools">
            @if($authorization_source_gatekeeper != null)
                <a href="/gatekeepers/{{ $authorization_source_gatekeeper->id }}" title="View Gatekeeper">
                    <span class="badge badge-info badge-large">Shared from {{ $authorization_source_gatekeeper->name }}</span>
                </a>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if($gatekeeper->is_default == 1)
            <p>This gatekeeper is marked as default. All active users are authorized.</p>
        @endif

        <div class="row">
            <div class="col-md-6">
                <h5>Authorized Users</h5>
                <div class="table-responsive">
                    <table class="table table-striped" id="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                @if($gatekeeper->is_default != 1)
                                    <th>Date Authorized</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if($gatekeeper->is_default == 1)
                                @forelse($active_users as $user)
                                    <tr>
                                        <td><a href="/members/{{ $user->id }}/profile" title="View Profile">{{ $user->get_name() }}</a></td>
                                    </tr>
                                @empty
                                @endforelse
                            @else
                                @forelse($authorizations as $authorization)
                                    <tr>
                                        <td><a href="/members/{{ $authorization->user->id }}/profile" title="View Profile">{{ $authorization->user->get_name() }}</a></td>
                                        <td>{{ $authorization->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
