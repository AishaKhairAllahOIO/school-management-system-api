<?php

namespace App\Services\Notification;

use App\Models\DeviceToken;
use App\Models\User;

class DeviceTokenService{

 public function registerToken(User $user,$fcm_token){


        DeviceToken::updateOrCreate(
            ['fcm_token' => $fcm_token],
            [
                'user_id'      => $user->id,
                'last_used_at' => now(),
            ]
        );
 }

 public function deleteToken($fcm_token){
     DeviceToken::where('fcm_token', $fcm_token)->delete();

 }
}
