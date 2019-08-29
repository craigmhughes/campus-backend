<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Conversation;
use App\Message;

class MessagesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'message' => 'required|string|max:1000',
            'recipient' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        // Return if request does not meet requirements
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $conversation = Conversation::find($request['conversation_id']);

        // Block users not in conversation from updating
        if($conversation == null){

            // Return existing conversations, if any exist
            $chats_with_receiver = Conversation::where(function($conv){
                $conv->where('sender_id', Auth::user()['id'])->where('receiver_id', request()['recipient']);
            })->orWhere(function($conv){
                $conv->where('receiver_id', Auth::user()['id'])->where('sender_id', request()['recipient']);
            })->get();

            if(count($chats_with_receiver) > 0){
                return response()->json(["error" => "conversation id does not match"], 401);
            }

            $conversation = Conversation::create([
                "receiver_id" => $request["recipient"], 
                "sender_id" => Auth::user()['id']
            ]);

            $message = Message::create([
                "sender_id" => Auth::user()['id'],
                "message" => $request["message"]
            ]);
            
        } else if (!($conversation['sender_id'] == Auth::id() || $conversation['receiver_id'] == Auth::id())){
            return response()->json(["error" => "not authorized"], 401);
        }

        $message = Message::create([
            "sender_id" => Auth::user()['id'],
            "message" => $request["message"]
        ]);

        $message->conversation_id = $conversation["id"];
        $message->save();

        return response()->json(["success" => $message], 201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
