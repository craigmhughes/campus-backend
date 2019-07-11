<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
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
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Create new user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(){

        $data = request(['name', 'email', 'password', 'password_confirmation']);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'=> ['min:6','required_with:password_confirmation','same:password_confirmation'],
            'password_confirmation' => ['min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        // Run login function to return API access token.
        return $this->login();
    }

    public function update_user(Request $request){

        // return $request;

        $data = request(['profile_image']);

        $validator = Validator::make($data, [
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg', 'max:1999'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        $user = auth()->user();

        if($request->hasFile('profile_image')){

            // return response()->json(true, 200);
            
            $file = request()->file('profile_image')->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $ext = request()->file('profile_image')->getClientOriginalExtension();

            $nameToSave = $filename.'_'.time().'.'.$ext;
            $path = request()->file('profile_image')->storeAs('public/profile_images', $nameToSave);
        
            $user->profile_image = $path;

        }

        $user->save();
        return response()->json($user, 200);
        
    }
}