<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersStatusesTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedApplicantStatuses();
        $this->seedApplicantDeniedStatuses();
        $this->seedApplicantAbandonedStatuses();
        $this->seedActiveStatuses();
        $this->seedSuspendedStatuses();
        $this->seedHiatusStatuses();
        $this->seedInactiveStatuses();
        $this->seedTerminatedStatuses();
    }

    private function seedApplicantStatuses(): void
    {
        User::query()
            ->whereIn('status', [
                UserStatus::STATUS_APPLICANT,
                UserStatus::STATUS_APPLICANT_DENIED,
                UserStatus::STATUS_APPLICANT_ABANDONED,
                UserStatus::STATUS_ACTIVE,
                UserStatus::STATUS_SUSPENDED,
                UserStatus::STATUS_HIATUS,
                UserStatus::STATUS_INACTIVE,
                UserStatus::STATUS_TERMINATED,
            ])
            ->get()
            ->each(function (User $user) {
                $appliedDate = now()->subDays(random_int(5, 1460));
                UserStatus::query()->create([
                    'user_id' => $user->id,
                    'status' => UserStatus::STATUS_APPLICANT,
                    'created_at' => $appliedDate,
                ]);
            });
    }

    private function seedApplicantDeniedStatuses(): void
    {
        User::query()
            ->where('status', UserStatus::STATUS_APPLICANT_DENIED)
            ->get()
            ->each(function (User $user) {
                UserStatus::query()->create([
                    'user_id' => $user->id,
                    'status' => UserStatus::STATUS_APPLICANT_DENIED,
                    'created_at' => $user->status_history()
                        ->where('status', UserStatus::STATUS_APPLICANT)
                        ->first()
                        ->created_at
                        ->addDays(random_int(5, 7)),
                ]);
            });
    }

    private function seedApplicantAbandonedStatuses(): void
    {
        User::query()
            ->where('status', UserStatus::STATUS_APPLICANT_ABANDONED)
            ->get()
            ->each(function (User $user) {
                UserStatus::query()->create([
                    'user_id' => $user->id,
                    'status' => UserStatus::STATUS_APPLICANT_ABANDONED,
                    'created_at' => $user->status_history()
                        ->where('status', UserStatus::STATUS_APPLICANT)
                        ->first()
                        ->created_at
                        ->addDays(random_int(7, 60)),
                ]);
            });
    }

    private function seedActiveStatuses(): void
    {
        User::query()
            ->whereIn('status', [
                UserStatus::STATUS_ACTIVE,
                UserStatus::STATUS_SUSPENDED,
                UserStatus::STATUS_HIATUS,
                UserStatus::STATUS_INACTIVE,
                UserStatus::STATUS_TERMINATED,
            ])
            ->get()
            ->each(function (User $user) {
                UserStatus::query()->create([
                    'user_id' => $user->id,
                    'status' => UserStatus::STATUS_ACTIVE,
                    'created_at' => $user->status_history()
                        ->where('status', UserStatus::STATUS_APPLICANT)
                        ->first()
                        ->created_at
                        ->addDays(random_int(5, 7)),
                ]);
            });
    }

    public function seedSuspendedStatuses(): void
    {
        $this->seedStatusAfterStatus(UserStatus::STATUS_SUSPENDED, UserStatus::STATUS_ACTIVE);
    }

    public function seedHiatusStatuses(): void
    {
        $this->seedStatusAfterStatus(UserStatus::STATUS_HIATUS, UserStatus::STATUS_ACTIVE);
    }

    private function seedInactiveStatuses(): void
    {
        $this->seedStatusAfterStatus(UserStatus::STATUS_INACTIVE, UserStatus::STATUS_ACTIVE);
    }

    private function seedTerminatedStatuses(): void
    {
        $this->seedStatusAfterStatus(UserStatus::STATUS_TERMINATED, UserStatus::STATUS_ACTIVE);
    }

    private function seedStatusAfterStatus(string $newStatus, $oldStatus): void
    {
        User::query()
            ->where('status', $newStatus)
            ->get()
            ->each(function (User $user) use ($oldStatus, $newStatus) {
                UserStatus::query()->create([
                    'user_id' => $user->id,
                    'status' => $newStatus,
                    'created_at' => $this->randomDateBetween(
                        $user->status_history()
                            ->where('status', $oldStatus)
                            ->first()
                            ->created_at,
                        now()
                    ),
                ]);
            });
    }

    private function randomDateBetween(Carbon $from, Carbon $to): Carbon
    {
        return $from->addDays(random_int(1, $from->diffInDays($to)));
    }
}
