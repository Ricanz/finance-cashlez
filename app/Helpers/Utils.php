<?php

namespace App\Helpers;

use App\Models\Applicant;
use App\Models\DokumenApplicant;
use App\Models\HistoryApproval;
use App\Models\MasterPrivilege;
use App\Models\Merchant;
use App\Models\MerchantDocument;
use App\Models\MerchantPayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;
use Image;

class Utils
{
    public static function uploadImageOri($image)
    {
        try {
            $imageName = time() . uniqid() . '.' . $image->extension();
            if (env('APP_ENV') == 'production' || !env('APP_ENV')) {
                $image->move('images', $imageName);
            } else {
                $image->move(public_path('images'), $imageName);
            }
            $path = url('images/' . $imageName);
            return $path;
        } catch (\Throwable $th) {    
            return false;
        }
    }

    public static function uploadFile($image, $uuid)
    {
        try {
            $imageName = $uuid . '.' . $image->extension();
            if (env('APP_ENV') == 'production' || !env('APP_ENV')) {
                $image->move('images', $imageName);
            } else {
                $image->move(public_path('images'), $imageName);
            }
            $path = url('images/' . $imageName);
            return $path;
        } catch (\Throwable $th) {    
            return false;
        }
    }

    public static function generateToken()
    {
        return csrf_token();
    }

}
