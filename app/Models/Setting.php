<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends UuidModel {
    protected $table        = 'tb_setting';
    protected $primaryKey   = 'setting_id';
    public    $incrementing = false;
    protected $fillable     = [
        'company_id', 'auth_type', 'setting_guide', 'setting_key', 'setting_value', 'description'
    ];
    protected $hidden       = ['setting_id'];
    protected $dates        = ['deleted_at'];

    public function company() {
        return $this->belongsTo('App\Models\Company', 'company_id', 'company_id');
    }
}
