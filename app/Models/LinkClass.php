<?php
//会员等级
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkClass extends Model{
	protected $table        = 'tb_link_class';
	protected $primaryKey   = 'id';
	protected $fillable     = ['id','parent_id','title','sorts'];

}
