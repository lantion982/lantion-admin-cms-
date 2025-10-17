<?php
    //管理员信息
    namespace App\Models;
    
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Zizaco\Entrust\Traits\EntrustUserTrait;
    
    class Admin extends Authenticatable{
        use EntrustUserTrait;
        use Notifiable;
        protected $table      = 'tb_admin';
        protected $primaryKey = 'id';
        protected $fillable   = [
            'login_name','login_pwd','display_name','phone','email','sorts','is_admin','is_show','is_allow','failed_count','register_ip'];
        protected $hidden = ['remember_token','deleted_at'];
        
        //定义多对多关系的字段
        public function roles(){
            return $this->belongsToMany('App\Models\Role','tb_role_user','user_id','role_id' );
        }
     
        public function getRolesAttribute(){
            return $this->roles()->pluck('id')->all();
        }
        
        public function getAuthPassword(){
            return $this->attributes['login_pwd'];
        }
        
        public function getAuthIdentifier(){
            return $this->attributes['id'];
        }
        
        public function getName(){
            return $this->attributes['login_name']."(".$this->attributes['display_name'].")";
        }
    }
