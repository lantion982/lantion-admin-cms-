<?php
//会员等级
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model{
	use SoftDeletes;
	protected $table        = 'tb_level';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
	protected $fillable     = ['id','level_code','level_name','gift_money','is_special','is_default','remarks'];
	protected $hidden       = ['deleted_at'];

	public function members(){
		return $this->hasMany('App\Models\Member','level_id','id');
	}

}
