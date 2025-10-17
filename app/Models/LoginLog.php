<?php
    //登录日志
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class LoginLog extends Model{
        use SoftDeletes;
        protected $table      = 'tb_login_log';
        protected $primaryKey = 'id';
        protected $softDelete = true;
        protected $fillable   = [
            'member_id','member_type','login_name','login_ip','login_area','login_result','remarks',
        ];
        
        public function member(){
            return $this->belongsTo('App\Models\Member','member_id','id');
        }
    
        public function admin(){
            return $this->belongsTo('App\Models\Admin','member_id','id');
        }
    }
