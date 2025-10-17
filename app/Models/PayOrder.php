<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayOrder extends Model{
	use SoftDeletes;
	protected $table        = 'tb_pay_order';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
	protected $fillable     = [
		'id','member_id','admin_id','bill_no','bank_account_id','pay_account_id','pay_code','pay_money','pay_status','remarks','accept_time'
	];
	protected $hidden       = ['deleted_at'];

	public function bankAccount(){
		return $this->belongsTo('App\Models\BankAccount','bank_account_id','id');
	}

	public function admin(){
		return $this->belongsTo('App\Models\Admin','admin_id','id');
	}

	public function member(){
		return $this->belongsTo('App\Models\Member','member_id','id');
	}

	public function members(){
		return $this->morphTo();
	}

	/*public function payAccount(){
		return $this->belongsTo('App\Models\PayAccount','pay_account_id','id');
	}*/
}
