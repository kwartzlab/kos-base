<?php

namespace App\Http\Controllers;

use App\Http\Requests\SlackInviteRequest;
use Illuminate\Http\RedirectResponse;

class SlackInviteController extends Controller
{
    public function __invoke(SlackInviteRequest $request): RedirectResponse
    {
        return redirect(config('services.slack.auto_invite.url'));
    }
}
