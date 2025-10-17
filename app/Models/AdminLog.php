<?php

namespace App\Models;

use  Illuminate\Database\Eloquent\Model;
class AdminLog extends Model {
    protected $table      = 'tb_admin_log';
	protected $primaryKey = 'id';
    protected $fillable   = ['id','admin_id','content','optype','ip_addr'];

    public function admin() {
        return $this->belongsTo('App\Models\Admin','admin_id','id');
    }
}
