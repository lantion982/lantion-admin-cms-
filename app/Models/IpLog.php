<?php
    //登录注册IP 记录
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class IpLog extends Model{
        use SoftDeletes;
        protected $table      = 'tb_ip_log';
        protected $primaryKey = 'id';
        protected $softDelete = true;
        protected $fillable   = ['id','ip_addr','domain','register_count','failed_count'];
      
    }
