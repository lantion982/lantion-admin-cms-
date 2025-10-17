<?php
//会员等级
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownClass extends Model{
	protected $table        = 'tb_down_class';
	protected $primaryKey   = 'id';
	protected $fillable     = ['id','parent_id','title','sorts','is_show'];
}
