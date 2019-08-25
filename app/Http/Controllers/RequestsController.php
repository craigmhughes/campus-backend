<?php

namespace App\Http\Controllers;

use App\Events\RequestUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\User;
use App\Request as UserRequest;

class RequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $requests = UserRequest::where('requested_user', auth()->id())->get();
        $user_list = [];

        if (request()["count"]){
            return response()->json(["count" => "returning count"], 200);
        }

        // Add total users to list
        foreach($requests as $request){
            // Get user returns array, get first find of array
            array_push($user_list, User::where('id', $request["user_id"])->get()[0]);
        }

        return response()->json($user_list, 200);
    }

    /**
     * Display length of requests
     *
     * @return \Illuminate\Http\Response
     */
    public function count()
    {
        $requests = UserRequest::where('requested_user', auth()->id())->get();
        $user_list = [];

        // Add total users to list
        foreach($requests as $request){
            // Get user returns array, get first find of array
            array_push($user_list, User::where('id', $request["user_id"])->get()[0]);
        }

        return response()->json(count($user_list), 200);
    }

    public function show(){

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'requested_user' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $request = UserRequest::create($request->all());
        $request->user_id = Auth::user()['id'];
        $request->requested_user = $request['requested_user'];
        $request->save();

        return response()->json($request, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        // Remove from user's list
        UserRequest::where("user_id", auth()->id())->where("requested_user", $request["requested_user"])->delete();

        //  Remove user from deleted user's list
        UserRequest::where("requested_user", auth()->id())->where("user_id", $request["requested_user"])->delete();

        return response()->json(200);
    }
}
