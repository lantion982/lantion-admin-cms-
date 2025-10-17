<?php
    //操作备注
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class AdminCommit extends Model{
        use SoftDeletes;
        protected $table      = 'tb_admin_commit';
        protected $softDelete = true;
        protected $fillable   = ['id','member_id','admin_id','commit_type','commits'];
        protected $hidden     = ['deleted_at'];
        
        public function admin(){
            return $this->belongsTo('App\Models\Admin','admin_id','id');
        }
    }
