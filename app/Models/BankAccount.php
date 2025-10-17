<?php
//绑定银行卡信息
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model{
    use SoftDeletes;
    protected $table        = 'tb_bank_account';
    protected $primaryKey   = 'id';
    protected $softDelete   = true;
    protected $fillable     = [
        'id','member_id','bank_code','account_name','account_number','opening_bank','opening_address','is_default','is_locked','sorts'
    ];
    protected $hidden       = ['deleted_at'];

    public function member() {
        return $this->morphTo();
    }
}
