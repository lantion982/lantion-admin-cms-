<?php
//短信发送日志
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SmsLog extends Model {
    use SoftDeletes;
    protected $table      = 'tb_sms_log';
    protected $primaryKey = 'id';
    protected $softDelete = true;
    protected $fillable   = ['id','phone','code','time_send','time_out','send_total','send_today','is_active'];
}
