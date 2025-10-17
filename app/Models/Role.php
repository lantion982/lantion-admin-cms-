<?php
    
    namespace App\Models;
    
    use Zizaco\Entrust\EntrustRole;
    
    class Role extends EntrustRole{
        protected $table      = 'tb_rbac_roles';
        protected $primaryKey = 'id';
        protected $softDelete = true;
        protected $fillable   = ['id','title','remarks','sorts'];
        
        //定义多对多关系，才能删除关联表:tb_role_user 数据
        public function users(){
            return $this->belongsToMany('App\Models\Admin','tb_role_user','role_id','user_id');
        }
    }
