<?php

namespace App\Helpers;

use App\Models\Applicant;
use App\Models\DokumenApplicant;
use App\Models\HistoryApproval;
use App\Models\MasterPrivilege;
use App\Models\Merchant;
use App\Models\MerchantDocument;
use App\Models\MerchantPayment;
use App\Models\Privilege;
use App\Models\Role;
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

    public static function countTotalRoles($roleId)
    {
        return User::where('role', $roleId)->where('status', 'active')->count();
    }

    public static function restOfPrivilege($roleId)
    {
        $privilege = Privilege::where('role_id', $roleId)->where('status', 'active')->count();
        return 5 - $privilege;
    }

    public static function calculateMerchantPayment($bankTransfer, $feeMdrMerchant, $feeBankMerchant, $taxPayment)
    {
        $calculate = $bankTransfer - (($feeMdrMerchant - $feeBankMerchant) + $taxPayment);
        return $calculate;
    }

    public static function calculateTreshold($trxCount)
    {
        return (2 + 1) * $trxCount;
    }

    public static function getStatusReconcile($treshold, $boSettlement, $bankSettlement)
    {
        if (($bankSettlement - $boSettlement) < $treshold &&
            ($bankSettlement - $boSettlement) > (0 - $treshold)
        ) {
            return "MATCH";
        } else {
            return "NOT_MATCH";
        }
    }

    public static function getPrivilege($desc)
    {
        $user = Auth::user();
        $data = Privilege::where('description', $desc)->where('role_id', $user->role)->first();
        return $data;
    }

    public static function getRoleName($roleId)
    {
        $roleName = Role::where('id', $roleId)->pluck('title')->first();
        return $roleName;
    }

    public static function customRound($number)
    {
        // $integerPart = intval($number);
        $integerPart = floor($number);

        $decimalPart = $number - $integerPart;
        // dd($number, $integerPart, $decimalPart);

        $roundedDecimal = round($decimalPart, 2);
        // $roundedDecimal = round($decimalPart, 1);

        if ($roundedDecimal >= 0.5) {
            return ceil($number);
        } else {
            return floor($number);
        }
    }
}
