<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Connection;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class ConnectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $connections = Connection::where('user_id', auth()->id())->get();
        $user_list = [];

        // Add total users to list
        foreach($connections as $connection){
            // Get user returns array, get first find of array
            array_push($user_list, User::where('id', $connection["connected_user"])->get()[0]);
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
            'connected_user' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $connection = Connection::create($request->all());
        $connection->user_id = Auth::user()['id'];
        $connection->connected_user = $request['connected_user'];
        $connection->save();

        return response()->json($connection, 201);
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
        Connection::where("user_id", auth()->id())->where("connected_user", $request["connected_user"])->delete();

        //  Remove user from deleted user's list
        Connection::where("connected_user", $request["connected_user"])->where("user_id", auth()->id())->delete();

        return response()->json(200);
    }
}
