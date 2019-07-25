<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Storage;

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
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:1999'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'uni' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        $user = auth()->user();

        if($request->hasFile('profile_image')){
            
            $file = request()->file('profile_image')->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $ext = request()->file('profile_image')->getClientOriginalExtension();

            $nameToSave = $filename.'_'.time().'.'.$ext;
            
            // Stored path will be in public storage
            $path = request()->file('profile_image')->storeAs('public/profile_images', $nameToSave);

            // Create var and store old profile picture location here.
            $former_filename = null;
            preg_match("/[^\/]*\.(\w+)$/", $user->profile_image, $former_filename);
            
            // Delete old profile picture -- NOTE: Must sleep if user spam uploads
            // it will delete new uploads too.
            if (count($former_filename) > 0){
                sleep(1);
                Storage::delete("public/profile_images/".$former_filename[0]);
            }
            
            
            // accessible url will be different to stored url
            $user->profile_image = "storage/profile_images/".$nameToSave;

        }

        if($request->has("name")){
            $user->name = $request["name"];
        }

        if($request->has("email")){
            $user->email = $request["email"];
        }

        if($request->has("uni_name")){
            $user->uni_name = $request["uni_name"];
        }

        if($request->has("mentor_subject")){
            $user->mentor_subject = $request["mentor_subject"];
        }

        if($request->has("mentee_subject")){
            $user->mentee_subject = $request["mentee_subject"];
        }

        $user->save();
        return response()->json($user, 200);
        
    }
}