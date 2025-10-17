<?php
    /*
	|--------------------------------------------------------------------------
	| 登录
	|--------------------------------------------------------------------------
	*/
    
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Libs\UserHelper;
    use App\Models\Admin;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Illuminate\Foundation\Auth\AuthenticatesUsers;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Validator;
    
    class LoginController extends Controller{
        
        use AuthenticatesUsers;
        protected $redirectTo = '/manager/home';
        
        public function __construct(){
            $this->middleware('guest:admin',['except' => ['logout','getLogout']]);        //跳转值守，排除的方法
        }
        
        public function showLoginForm(){
            return view('manager.auth.login');
        }
        
        public function mylogin(Request $request){
            $data = $request->except(['_token','_url']);
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
            
            //ip检查
            $ret = Helper::checkIpLoginFailed($ipAddr,$_SERVER['HTTP_HOST']);
            if($ret['result']==false){
                return response()->json(['status' => FAILED,'msg' => $ret['message']]);
            }
            
            //检查登录帐号是否存在
            $admin = Admin::where(['login_name' => $data['login_name']])->first();
            
            if($admin){
                //用户登录次数检查
                $ret = UserHelper::checkAdminLogin($data['login_name']);
                if($ret['result']==false){
                    return response()->json(['status' => FAILED,'msg' => $ret['message']]);
                }
                unset($data['captcha']);
                unset($data['remember']);
                if(Auth::guard('admin')->attempt($data,$request->has('remember'))){
                    Helper::recordLogLogin($admin->id,'App\Models\Admin',$admin->login_name,$ipAddr,'success');
                    Helper::updateIpLoginSuccess($ipAddr);
                    UserHelper::adminLoginSuccess($admin->id);
                    $res = $this->sendLoginResponse($request);
                    return response()->json(['status' => SUCCESS,'msg' => '登录成功！']);
                }
                Helper::recordLogLogin($admin->id,'App\Models\Admin',$admin->login_name,$ipAddr,'failed');
                Helper::updateIpLoginFailed($ipAddr);
                $count = UserHelper::adminLoginFailed($admin->id);
                $failCount = config('auth.LOGIN_FAILED_COUNT',5);
                return response()->json(['status' => FAILED,'msg' => '帐号密码错误，错误次数'.$count.'次，超过'.$failCount.'次将锁定账号！']);
            }
            Helper::updateIpLoginFailed($ipAddr);
            return response()->json(['status' => FAILED,'msg' => '帐号密码错误，登录失败！']);
        }
        
        public function getLogout(Request $request){
            return $this->logout($request);
        }
        
        public function logout(Request $request){
            Auth::guard('admin')->logout();
            //$request->session()->flush();
            //$request->session()->regenerate();
            return redirect('/manager/login');
        }
    }
