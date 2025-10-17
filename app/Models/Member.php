<?php
//会员信息
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Member extends Authenticatable{
	use HasApiTokens,Notifiable;
	protected $table        = 'tb_member';
	protected $primaryKey   = 'id';
	protected $softDelete   = true;
	protected $fillable     = [
		'id','agent_id','is_agent','level_id','login_name','login_pwd','nick_name','sex','phone','birthday','email','is_reg','register_time','register_ip',
        'register_domain','register_area','late_login_time','late_login_ip','late_login_area','balance','points','is_allow','failed_count','domain'
	];
	protected $hidden  = ['remember_token',];
	
	
	public function getAuthPassword(){
		return $this->attributes['login_pwd'];
	}

	public function getAuthIdentifier(){
		return $this->attributes['id'];
	}

	public function payOrder(){
		return $this->hasMany('App\Models\PayOrder','id','member_id');
	}
	
	public function agent(){
		return $this->belongsTo('App\Models\Agent','agent_id','id');
	}
	
	public function memberLevel(){
		return $this->belongsTo('App\Models\Level','level_id','id');
	}

	public function bankAccounts(){
		return $this->morphMany('App\Models\BankAccount','member');
	}

	public function moneyMvmt(){
		return $this->morphMany('App\Models\MoneyLog','member');
	}
	
	//通过login_name 查找用户：
	public function findForPassport($username){
		return $this->where('login_name','=',$username)->first();
	}

	public function validateForPassportPasswordGrant($password){
		return \Hash::check($password,$this->login_pwd);
	}
}
