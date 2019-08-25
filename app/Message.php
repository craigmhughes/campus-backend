<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'message'
    ];

    protected $hidden = [
        'id', 'updated_at'
    ];

    public function conversation(){
        return $this->belongsTo('App\Conversation');
    }
}
