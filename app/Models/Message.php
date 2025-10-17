<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model {
    use SoftDeletes;
    protected $table        = 'tb_message';
    protected $primaryKey   = 'id';
    protected $softDelete   = true;
    protected $fillable     = [
        'id','member_id','login_name','from_uid','from_username','message_pid','message_body','message_read','ip_addr','readuid'
    ];

    public function fromUser() {
        return $this->belongsTo(Member::class,'from_uid','id');
    }
}
