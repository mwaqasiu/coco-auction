<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\Favorite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireAuction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-auction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $expiryDate = Carbon::now()->addMinutes(15)->setSeconds(0);
        $expiredAds = Ad::where('expired_at', $expiryDate)->get();


        foreach ($expiredAds as $expiredAd) {

            $checkIsFavourites = Favorite::where('ad_id', $expiredAd->id)->get();
            if ($checkIsFavourites) {
                foreach ($checkIsFavourites as $checkIsFavourite) {
                    $users = User::where('id', $checkIsFavourite->user_id)->get();
                    foreach ($users as $user) {
                        log::info($user);
                        $data = array('name' => "$user->name");
                        Mail::send('mail', $data, function ($message) use ($data, $user) {
                            $message->to($user->email, 'Larabid')->subject('Auction Expired');
                        });
                    }
                }
            }
        }
    }
}
