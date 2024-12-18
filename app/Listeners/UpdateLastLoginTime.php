<?php

namespace App\Listeners;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

class UpdateLastLoginTime
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        User::where('id', $user->id)->update([
            'last_login_at' => Carbon::now(),
        ]);

        if(!$user->getRoleNames()->first()) {
            $user->assignRole('user');
        }
    }
}
