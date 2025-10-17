<?php
//反馈信息
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedBack extends Model {
	use SoftDeletes;
	protected $table        = 'tb_feedback';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
}
