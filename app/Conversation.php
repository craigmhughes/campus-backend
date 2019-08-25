<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'receiver_id'
    ];

    public function messages(){
        return $this->hasMany('App\Message', 'conversation_id');
    }

}
