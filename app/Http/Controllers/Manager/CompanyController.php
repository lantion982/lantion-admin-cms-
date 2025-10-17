<?php

namespace App\Http\Controllers\Manager;

use App\Libs\RoomHelper;
use App\Models\Agent;
use App\Models\Company;
use App\Libs\Helper;
use App\Models\Group;
use App\Models\MoneyLog;
use App\Models\Setting;
use App\Repository\RBACRoleRepository;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mockery\Exception;
use Ramsey\Uuid\Uuid;

class CompanyController extends Controller{
    protected $adminId;
    protected $role;

    //公司相关
    public function __construct(RBACRoleRepository $role){
        parent::__construct();
        $this->middleware(function($request,$next){
            $this->adminId = Auth::guard('admin')->user()->id;
            return $next($request);
        });
        $this->role = $role;
    }

    public function company(Request $request){
        return redirect('/manager/companyManage');
    }

    public function companyManage(Request $request){
        $page_title['type'] = 'Title';
        $page_title['content'] = '顶级代理';
        $current_page = '1';
        if(isset($request['page'])){
            $current_page = $request['page'];
        }
        $data = $request->except(['_token','page']);
        $page_count = 100;
        if(array_key_exists('page_count',$data) && $data['page_count'] != ''){
            $page_count = $data['page_count'];
        }
        $companies = Company::orderBy('company_sort','ASC')->paginate($page_count);
        if($request->ajax()){
            return View::make('manager.company.listAjax')
                ->with(['companies' => $companies,'page_title' => $page_title,'current_page' => $current_page])
                ->render();
        }else{
            return view('manager.company.list',compact('companies','page_title','current_page'));
        }
    }

    public function addCompany(Request $request){
        return View::make('manager.company.info')
            ->with(['company' => null])->render();
    }

    public function createCompany(Request $request){
        $member_prefix = $request['member_prefix'];
        $ret = Company::where(['member_prefix' => $member_prefix])->first();
        if($ret){
            $data['status'] = FAILED;
            $data['msg'] = '会员前缀已被占用!';
            return $data;
        }
        try{
            DB::beginTransaction();
            $data = $request->except(['_token','company_id']);
            $data['remain_amount'] = $data['quota_amount'];
            $data['regain_amount'] = $data['quota_amount'];
            $data['company_id'] = Uuid::uuid4()->getHex();;

            Company::create($data);
            $acdata['login_name'] = strtolower(str_random(5));
            $acdata['login_pwd'] = bcrypt('123456');
            $acdata['company_id'] = $data['company_id'];
            $acdata['agent_id'] = $data['company_id'];
            $acdata['level'] = 0;
            $acdata['is_active'] = 1;

            $acdata['agent_id'] = $data['company_id'];
            Agent::create($acdata);

            $gpdata['group_code'] = 'Default';
            $gpdata['group_name'] = $data['name'] . '默认分组';
            $gpdata['company_id'] = $data['company_id'];
            $gpdata['description'] = $data['name'] . '预设默认分组，不允许删除';
            Group::create($gpdata);
            DB::commit();
            return response()->json(['status' => SUCCESS,'msg' => '新增成功!']);
        }catch(Exception $exception){
            DB::rollBack();
            return response()->json(['status' => FAILED,'msg' => $exception->getMessage()]);
        }
    }

    public function companyAmountInfo(Request $request){
        $moneyTypes     = config('enums.admin_operate_type');
        $takeEffectTime = ['next_month' => '下月生效','instantly' => '即刻生效'];
        $company = Company::find($request['company_id']);
        if(empty($company)){
            return response()->json(['status' => FAILED,'msg' => '该公司信息未找到!']);
        }
        return View::make('manager.company.companyAjaxCompanyMoney')
            ->with(['company' => $company,'moneyTypes' => $moneyTypes,'takeEffectTime' => $takeEffectTime])->render();
    }

    //初始化公司角色
    public function initCompanyRole(Request $request){
        $company = Company::find($request['company_id']);
        $roles = $this->role->findWhere(['company_id' => $company->company_id]);
        foreach($roles as $key => $role){
            $this->role->delete($role->id);
        }
        if(empty($company)){
            return response()->json(['status' => FAILED,'msg' => '该公司信息未找到!','url' => '/manager/company']);
        }
        $roles = $this->role->findWhere(['company_id' => 'company_super']);
        foreach($roles as $key => $role){
            $data['name'] = $company->member_prefix . '_' . $role->name;
            $data['display_name'] = $role->display_name;
            $data['company_id'] = $company->company_id;
            $data['role_sort'] = $company->role_sort;
            $result = $this->role->create($data);

            //复制权限
            $this->role->savePermissions($result->id,$this->role->rolePermissionsIds($role->id));
        }
        return response()->json($company ? ['status' => SUCCESS,'msg' => '更新成功!'] : [
            'status' => FAILED,'msg' => '更新失败!'
        ]);
    }

    public function updateCompanyMoney(Request $request){
        $company = Company::find($request['company_id']);
        if(empty($company)){
            return response()->json(['status' => FAILED,'msg' => '该公司信息未找到!','url' => '/manager/company']);
        }
        $commit = Helper::saveAdminCommit($request->all());
        if(!$commit){
            return response()->json(['status' => FAILED,'msg' => '添加备注失败!']);
        }
        if($request['takeEffectTime'] =='next_month'){                                                                  //下月生效
            if($request['operation_type'] === 'admin_money_inc'){
                $company->regain_amount = $company->regain_amount+$request['money'];
            }else{
                $company->regain_amount = $company->regain_amount-$request['money'];
            }
            $company->save();
        }else{
            if($request['operation_type'] === 'admin_money_inc'){
                $company->remain_amount = $company->remain_amount+$request['money'];
            }else{
                $company->remain_amount = $company->remain_amount-$request['money'];
            }
            $company->save();
            $array = [
                'company_id'         => $request['company_id'],
                'admin_id'           => $this->adminId,
                'member_agent_id'    => $request['company_id'],
                'member_agent_type'  => 'App\Models\Agent',
                'money_before'       => 0,
                'money_after'        => 0,
                'money_change'       => 0,
                'quota_before'       => $company->quota_amount,
                'remain_before'      => $company->regain_amount,
                'move_type'          => $request['move_type'],
                'description'        => $request['commit'],
                'occur_time'         => date('Y-m-d H:i:s')
            ];

            $ret = MoneyLog::create($array);

            Helper::addAdminLog($this->adminId,'更新配额，金额变动编号： ' . $ret->money_movement_id,'update');
        }

        return response()->json($company ? ['status' => SUCCESS,'msg' => '更新成功!'] : [
            'status' => FAILED,'msg' => '更新失败!'
        ]);
    }

    public function companyInfo(Request $request){
        $company = Company::find($request['company_id']);
        if(empty($company)){
            return response()->json(
                ['status' => FAILED,'msg' => '该代理信息未找到!','url' => '/company']);
        }
        return View::make('manager.company.info')
            ->with(['company' => $company])->render();
    }

    public function updateCompanyInfo(Request $request){
        $agentAccount = Company::find($request['company_id']);
        if(empty($agentAccount)){
            return response()->json(
                ['status' => FAILED,'msg' => '该代理信息未找到!','url' => '/agentAccount']);
        }

        if(empty($request['name'])){
            $result = $agentAccount->update($request->all());
        }else{
            $result = $agentAccount->update(array_filter($request->all()));
        }
        return response()->json($result ? ['status' => SUCCESS,'msg' => '更新成功!'] : [
            'status' => FAILED,'msg' => '更新失败!'
        ]);
    }
   

    public function updateCompanyBase(Request $request){
        $company_id = $request->input('company_id');
        $data = $request->except('_token','company_id');
        $data['WEB_SHOW_SLIDE']    = $request->input('WEB_SHOW_SLIDE',0);
        $data['WEB_SHOW_ACTIVITY'] = $request->input('WEB_SHOW_ACTIVITY',0);
        $data['WEB_SHOW_ABOUT']    = $request->input('WEB_SHOW_ABOUT',0);
        $data['WEB_SHOW_PLATFORM'] = $request->input('WEB_SHOW_PLATFORM',0);

        foreach($data as $key => $val){
            $settings = Setting::where('setting_key',$key)->where('company_id',$company_id)->first();
            if($settings){
                $settings->setting_value = $val;
                $settings->save();
            }else{
                $set_data['company_id'] = $company_id;
                $set_data['auth_type'] = 'company';
                $set_data['setting_key'] = $key;
                $set_data['setting_value'] = $val;
                $set_data['description'] = $key;
                Setting::create($set_data);
            }
        }
        Helper::updateSettingCache();
        return response()->json(['status' => SUCCESS,'msg' => '更新成功']);
    }
}
