<?php
//会员等级
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Down extends Model{
	use SoftDeletes;
	protected $table        = 'tb_down';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
	protected $fillable     = [
	    'id','cid','title','link','pic','path','ban_path','ban_key','content','click_count','down_count','money','vip',
        'is_show','is_hot','sorts'
    ];
	protected $hidden       = ['deleted_at'];

	public function downClass(){
        return $this->belongsTo('App\Models\DownClass','cid','id');
	}

}
