<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\User;
use App\Conversation;

class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Conversation::where('sender_id', auth()->id())->orWhere('receiver_id', auth()->id())->get(), 200);
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
            'receiver_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        // Return existing conversations, if any exist
        $chats_with_receiver = Conversation::where(function($conv){
            $conv->where('sender_id', Auth::user()['id'])->where('receiver_id', request()['receiver_id']);
        })->orWhere(function($conv){
            $conv->where('receiver_id', Auth::user()['id'])->where('sender_id', request()['receiver_id']);
        })->get();

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } else if (count($chats_with_receiver) > 0){
            return response()->json(["error" => "conversation exists"], 200);
        }

        $conversation = Conversation::create($request->all());
        $conversation->sender_id = Auth::user()['id'];
        $conversation->save();

        return response()->json($conversation, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $conversation = Conversation::find($id);

        // Block users not in conversation from viewing
        if(!($conversation['sender_id'] == Auth::user()['id'] || $conversation['receiver_id'] == Auth::user()['id'])){
            return response()->json(["error" => "not authorized"], 401);
        }

        return response()->json(["messages" => Conversation::find($id)->messages], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $conversation = Conversation::find($id);

        // Block users not in conversation from viewing
        if(!($conversation['sender_id'] == Auth::user()['id'] || $conversation['receiver_id'] == Auth::user()['id'])){
            return response()->json(["error" => "not authorized"], 401);
        }

        $conversation->delete();

        return response()->json(["success" => "conversation deleted"], 200);
    }
}
