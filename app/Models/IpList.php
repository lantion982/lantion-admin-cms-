<?php
//黑白名单
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpList extends Model {
    use SoftDeletes;
    protected $table        = 'tb_ip_list';
    protected $primaryKey   = 'id';
    protected $softDelete   = true;
    #public $incrementing   = true; #主键是否默认自增长,默认为自增长
    #public $timestamps     = false; #该模型是否被自动维护时间戳
    protected $fillable     = [
        'id','ip_addr','host_name','host_type','block_type','is_active','remarks','sorts'
    ];
    protected $dates        = ['deleted_at'];
}
