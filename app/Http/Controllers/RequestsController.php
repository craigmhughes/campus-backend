<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $requests = Request::where('requested_user', auth()->id())->get();
        $user_list = [];

        // Add total users to list
        foreach($requests as $request){
            // Get user returns array, get first find of array
            array_push($user_list, User::where('id', $request["requested_user"])->get()[0]);
        }

        return response()->json($user_list, 200);
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

        $request = request::create($request->all());
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
        request::where("user_id", auth()->id())->where("requested_user", $request["requested_user"])->delete();

        //  Remove user from deleted user's list
        request::where("requested_user", $request["requested_user"])->where("user_id", auth()->id())->delete();

        return response()->json(200);
    }
}
