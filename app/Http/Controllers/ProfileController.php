<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class ProfileController extends Controller
{
   // public function __construct()
   // {
   //     $this->middleware('auth');
   // }

    /**
     * Show QR code to enable 2FA
     */
    public function twofa()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        // generate a secret
        $secret = $google2fa->generateSecretKey();

        // generate the QR code, indicating the address 
        // of the web application and the user name
        // or email in this case
        $qr_code = $google2fa->getQRCodeInline(
            "cylab.be",
            //$user->email,
            'giacomo.santamaria@gmail.com',
            $secret
        );

        // store the current secret in the session
        // will be used when we enable 2FA (see below)
        session([ "2fa_secret" => $secret]);

        return view('profile.2fa', [
            "qr_code" => $qr_code]);
    }

    /**
     * check the submitted OTP
     * if correct, enable 2FA
     */
    public function twofaEnable(Request $request)
    {
        $google2fa = new Google2FA();

        // retrieve secret from the session
        $secret = session("2fa_secret");
        $user = Auth::user();

        if ($google2fa->verify($request->input('otp'), $secret)) {
            // store the secret in the user profile
            // this will enable 2FA for this user
            $user->twofa_secret = $secret;
            $user->save();

            // avoid double OTP check
            session(["2fa_checked" => true]);

            return redirect(action('ProfileController@twofa'));
        }

        throw ValidationException::withMessages([
            'otp' => 'Incorrect value. Please try again...']);
    }
}