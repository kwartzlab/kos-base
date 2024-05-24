<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class SlackInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $this->abortIfNotEnabled();
        $this->abortIfNoToken();
        $this->abortIfInvalidToken();
        $this->abortIfUserNotActive();

        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }

    private function abortIfNotEnabled(): void
    {
        if (! config('services.slack.auto_invite.enabled', false)) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }

    private function abortIfNoToken(): void
    {
        if (! $this->input('t', false)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }
    }

    private function abortIfInvalidToken(): void
    {
        try {
            if (! User::query()->find(Crypt::decrypt($this->input('t')))) {
                abort(Response::HTTP_UNAUTHORIZED);
            }
        } catch (DecryptException $exception) {
            abort(Response::HTTP_UNAUTHORIZED);
        }
    }

    public function abortIfUserNotActive(): void
    {
        $user = User::query()->find(Crypt::decrypt($this->input('t')));
        if ($user->status !== UserStatus::STATUS_ACTIVE) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }
}
