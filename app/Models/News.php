<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model {
    use SoftDeletes;
    protected $table        = 'tb_news';
    protected $primaryKey   = 'id';
    protected $softDelete   = true;
    protected $fillable     = ['id','cid','title','content','pic','outer_link','is_show','is_hot','sorts','begin_time','end_time'];
    protected $hidden       = ['deleted_at'];
}
