<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\nfckey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\LOG;
use JWTAuth;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'nfclogin', 'pinlogin']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        if ($token = JWTAuth::attempt($credentials)) {
            return $this->respondWithToken($token);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Login with NFC.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function nfclogin() {


        $getkey = request(['id', 'password']);
        $key = nfckey::where('id_tag', $getkey['id'])->first();

        $credentials = (['email' => $key['email'], 'password' => $key['password']]);

        if ($token = JWTAuth::attempt($credentials)) {
            return $this->respondWithToken($token);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
        

    //    $user = User::where('nfcid', $credentials['id'])->first();

    //    $credentials = (['email' => $user['email'], 'password' => $credentials['password']]);
    //    return response()->json($credentials);
    //    if (! $token = auth()->attempt($credentials)) {
    //        return response()->json(['error' => 'Unauthorized'], 401);
    //    }
    }

    /**
     * Login with PIN.
     *
     * @return \Illuminate\Http\JsonResponse
     */

     public function pinlogin() {

        $getkey = request(['pin']);
        $key = nfckey::where('pin', $getkey['pin'])->first();
        $credentials = (['email' => $key['email'], 'password' => $key['password']]);

        if ($token = JWTAuth::attempt($credentials)) {
            return $this->respondWithToken($token);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);

    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
     //   return $this->respondWithToken($token);
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'name'  =>  auth()->user()->name,
            'email' => auth()->user()->email,
            'id' => auth()->user()->id,
            'role' => auth()->user()->role,
            'id_azn_anagrafica' => auth()->user()->id_azn_anagrafica,
            'id_azn_puntovendita' => auth()->user()->id_azn_puntovendita,
            'active' => auth()->user()->active,
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }

    public function addprinter(Request $request) {
        $data = User::where('id', auth()->user()->id)->first();
        $data->printer = $request->printer;
        $data->update();

        return response()->json(true, 200, $headers);
    }

}
