<?php
//金额变动记录
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyLog extends Model{
    use SoftDeletes;
    protected $table        = 'tb_money_log';
    protected $primaryKey   = 'id';
    protected $softDelete   = true;
    protected $fillable     = ['id','member_id','admin_id','bill_no','move_type','money_before','money_change','money_after','remarks','sorts'];

    public function member() {
        return $this->belongsTo(Member::class,'member_id','id');
    }

    public function admin() {
        return $this->belongsTo(Admin::class,'admin_id','id');
    }
}
