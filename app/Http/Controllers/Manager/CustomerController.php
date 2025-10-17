<?php

namespace App\Http\Controllers\Manager;

use App\Models\FeedBack;
use Illuminate\Http\Auth;
use Illuminate\Http\Request;

class CustomerController extends Controller{
    protected $adminId;

    public function __construct(){
        parent::__construct();
        $this->middleware(function($request,$next){
            $this->adminId = Auth::guard('admin')->user()->id;
            return $next($request);
        });
    }

    public function feedBack(Request $request){
        $page_title['type']    = 'Search';
        $page_title['content'] = 'manager.customer.search';
        $data = $request->except(['_token','page']);
        $page = $request->input('page',1);

        $feedBack = FeedBack::orderBy('id','DESC')->where(function($query) use ($data){
            if(array_key_exists('keyword',$data) && trim($data['keyword']) != ''){
                $query->where(function($query) use ($data){
                    $query->orWhere('login_name','like','%' . trim($data['keyword']) . '%');
                    $query->orWhere('content','like','%' . trim($data['keyword']) . '%');
                });
            }
        })->paginate(20);

        if($request->ajax()){
            return View('manager.customer.listAjax',compact('feedBack','page_title','current_page','page_count'));
        }else{
            return view('manager.customer.list',compact('feedBack','page_title','current_page','page_count'));
        }
    }
}
