<?php
/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/
namespace App\Libs;

use App\Models\Admin;
use App\Models\Member;

class UserHelper{

    public static function memberAccount($param,$limit=null,$totalColumns=[],$columns=['*']){
        $builder = Member::where(function ($query) use ($param){
            if(array_key_exists('startDate',$param)&&$param['startDate']!=''){
                $query->where(function($query) use ($param){
                    $query->where('register_time','>=',$param['startDate']);
                });
            }
            if(array_key_exists('endDate',$param)&&$param['endDate']!=''){
                $query->where(function($query) use ($param){
                    $query->where('register_time','<=',$param['endDate']);
                });
            }
            if(array_key_exists('is_allow',$param)&&!is_null($param['is_allow'])&&$param['is_allow']!=2){
                $query->where('is_allow',$param['is_allow']);
            }
            if(array_key_exists('level_id',$param)&&!is_null($param['level_id'])){
                $query->where('level_id',$param['level_id']);
            }
            if(array_key_exists('keyword',$param)&&trim($param['keyword'])!=''&&!is_null($param['keyword'])){
                $query->where(function($query) use ($param){
                    $query->orWhere('login_name','like','%'.trim($param['keyword']).'%');
                    $query->orWhere('phone','like','%'.trim($param['keyword']).'%');
                    $query->orWhere('nick_name','like','%'.trim($param['keyword']).'%');
                    $query->orWhere('register_ip','like','%'.trim($param['keyword']).'%');
                    $query->orWhere('register_domain','like','%'.trim($param['keyword']).'%');
                });
            }
        })->orderBy('created_at', 'desc');
        $results = [];
        if(!empty($totalColumns)){
            foreach ($totalColumns as $totalColumn){
                $results[$totalColumn] = $builder->sum($totalColumn);
            }
        }
        $results['paginate'] = $builder->paginate($limit,$columns);
        return $results;
    }


    public static function checkMemberLogin($login_name) {
        $member = Member::where(['login_name' => $login_name])->first();
        $failed_count = config('auth.LOGIN_FAILED_COUNT',5);
        if($member->failed_count>=$failed_count){
            return ['result' => false,'content' => '',
                'message' => '密码错误次数超过'.$failed_count.'次被锁定，请联系客服人员解锁!',
            ];
        }else{
            if($member->is_allow==0){
                return ['result' => false,'content' => '','message' => '帐号已经被锁定，请联系客服解锁!'];
            }
        }
        return ['result' => true,'content' => '','message' => ''];
    }

    public static function memberLoginFailed($id) {
        $member = Member::where('id',$id)->first();
        $count  = $member->failed_count + 1;
        if($count >= config('auth.LOGIN_FAILED_COUNT',5)) {
            $member->update(['is_allow' => 0]);
        }
        $member->update(['failed_count' => $count]);
        $count = $member->failed_count;
        return $count;
    }

    public static function memberLoginSuccess($id,$login_ip) {
        $res = Member::where('id',$id)->update([
            'failed_count'    => 0,
            'late_login_ip'   => $login_ip,
            'late_login_time' => date('Y-m-d H:i:s'),
            'late_login_area' => Helper::getIpInfo($login_ip),
        ]);
    }

    public static function checkAdminLogin($name){
        $admin = Admin::where(['login_name'=>$name])->first();
        $failed_count = config('auth.LOGIN_FAILED_COUNT',5);
        if($admin->failed_count >= $failed_count){
            return ['result'=>false,'content'=>'','message'=>'登录错误超过'.$failed_count.'次已被锁定，请联系客服解锁!'];
        }else if($admin->is_allow == 0) {
            return ['result'=>false,'content'=>'','message'=>'帐号已被锁定，请联系客服解锁!'];
        }
        return ['result'=>true,'content'=>'','message'=>''];
    }
    
    public static function adminLoginFailed($id){
        $admin = Admin::where('id',$id)->first();
        $count = $admin->failed_count+1;
        if($count>=config('auth.LOGIN_FAILED_COUNT',5)){
            $res = Admin::where('id',$id)->update(['is_allow' => 0]);
        }
        $res = Admin::where('id',$id)->update(['failed_count' => $count]);
        return $count;
    }
    
    public static function adminLoginSuccess($id){
        $res = Admin::where('id',$id)->update(['failed_count' => 0]);
    }
    
    public static function updateLoginDomain($id,$domain){
        $res = Member::where('id',$id)->update(['domain' => $domain]);
    }
}
