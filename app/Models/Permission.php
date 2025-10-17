<?php
    
    namespace App\Models;
    
    use Zizaco\Entrust\EntrustPermission;
    
    class Permission extends EntrustPermission{
        protected $table = 'tb_permissions';
        protected $softDelete = true;
        protected $fillable = [
            'id','parent_id','name','title','icon','ptype','sorts','is_show',
        ];
        protected $appends = ['icon_html','sub_permission'];
        
        public function getIconHtmlAttribute(){
            return $this->attributes['icon']?'<i class="fa fa-'.$this->attributes['icon'].'"></i>':'';
        }
        
        public function getNameAttribute($value){
            if(starts_with($value,'#')){
                return head(explode('-',$value));
            }
            return $value;
        }
        
        public function setNameAttribute($value){
            $this->attributes['name'] = ($value=='#')?'#-'.time():$value;
        }
        
        public function getSubPermissionAttribute(){
            return $this->where('parent_id',$this->attributes['id'])->orderBy('sorts','asc')->get();
        }
    }
