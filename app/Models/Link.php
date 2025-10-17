<?php
//会员等级
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model{
	use SoftDeletes;
	protected $table        = 'tb_link';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
	protected $fillable     = ['id','title','link','cid','class_name','icon','remarks','sorts','is_hot','is_show'];
	protected $hidden       = ['deleted_at'];

	public function linkClass(){
        return $this->belongsTo('App\Models\LinkClass','cid','id');
	}

}
