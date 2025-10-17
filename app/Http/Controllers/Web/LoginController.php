<?php
    //云盘展示
    namespace App\Http\Controllers\Web;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Routing\Controller;
    use App\Libs\Helper;
    use App\Libs\UserHelper;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Validator;
    use App\Models\Member;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    
    class LoginController extends Controller{
        use AuthenticatesUsers;
        protected $redirectTo = '/plmm';
    
        public function __construct(){
            $this->middleware('guest:web',['except'=>['logout']]);        //跳转值守，排除的方法
        }
        
        public function showLoginForm(){
            return view('web.auth.login');
        }
    
        public function checkLogin(Request $request){
            $data   = $request->except(['_token','_url']);
            $ipAddr = Helper::getClientIP();
        
            $validator = Validator::make($data,[
                'login_name' => "required",
                'password'   => 'required',
                'captcha'    => 'required|captcha'],[
                'login_name.required' => '请输入登录帐号！',
                'password.required'   => '请输入登录密码！',
                'captcha.required'    => '请输入验证码！',
                'captcha.captcha'     => '验证码输入错误！',
            ]);
            if($validator->fails()){
                return response()->json(['status' => FAILED,'msg' => $validator->messages()->first()]);
            }
        
            //检查登录帐号是否存在
            $member = Member::where(['login_name'=>$data['login_name']])->first();
        
            if($member){
                
                $ret = UserHelper::checkMemberLogin($data['login_name']);           //用户登录次数检查
                if($ret['result'] == false){
                    return response()->json(['status' => FAILED,'msg' => $ret['message']]);
                }
                unset($data['captcha']);
                unset($data['remember']);
                if(Auth::guard('web')->attempt($data,$request->has('remember'))) {
                    Helper::recordLogLogin($member->id,'App\Models\Member',$member->login_name,$ipAddr,'success');
                    UserHelper::memberLoginSuccess($member->id,$ipAddr);
                    $res = $this->sendLoginResponse($request);
                    return response()->json(['status' =>SUCCESS ,'msg' => '登录成功！']);
                }
                Helper::recordLogLogin( $member->id,'App\Models\Member',$member->login_name,$ipAddr,'failed');
                $count = UserHelper::memberLoginFailed($member->id);
                $failCount = config('auth.LOGIN_FAILED_COUNT',5);
                return response()->json(['status' =>FAILED ,'msg' => '帐号密码错误！已错误次数'.$count.'次，超过'.$failCount.'次将锁定账号！']);
            
            }
            return response()->json(['status' =>FAILED ,'msg' => '帐号密码错误，登录失败！']);
        }
    
    
        public function logout(Request $request) {
            Auth::guard('web')->logout();
            //$request->session()->flush();
            //$request->session()->regenerate();
            return redirect('/login');
        }
    
    }
