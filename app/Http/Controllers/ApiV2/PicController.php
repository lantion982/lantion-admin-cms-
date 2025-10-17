<?php
    namespace App\Http\Controllers\ApiV2;
    
    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller as BaseController;
    use Illuminate\Support\Facades\Log;

    class PicController extends BaseController{
        
        //保存远程图片
        public function uploadPic(Request $request){
            $data = $request->all();
            $lpic = $this->savePic($data['picurl'],'plmm');
            Log::info('【保存远程图片】'.$lpic);
            return response()->json(['status'=>1,'content'=>$lpic,'msg'=>'success']);
        }
    
        public function savePic($url,$path,$key=0){
            $parse_url = parse_url($url);
            $pathinfo  = pathinfo($parse_url['path']);
            $allExt    = array('jpg','jpeg','png','gif','svg','webp','bmp');
            $fileExt   = $pathinfo['extension']??'jpg';
            if(!in_array($fileExt,$allExt)){
                $fileExt = 'jpg';
            }
            $fileName  = 'pic'.date('mdHis').$key.'.'.$fileExt;
            $localPath = public_path('/uploads/'.$path.'/'.date('Ymd').'/');
            if(!file_exists($localPath)){
                mkdir($localPath,0777,true);
            }
            $fullPath  = '/uploads/'.$path.'/'.date('Ymd').'/'.$fileName;
            /*使用curl 性能较好
            $downImg = file_get_contents("compress.zlib://".$icon);
			file_put_contents(public_path($fullPath),$downImg);
            */
            $fp = fopen(public_path($fullPath), 'w+');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            //跳过 https ssl 验证
            if(strlen($url)>5&&strtolower(substr($url,0,5))=="https"){
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSLVERSION, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            $file = curl_exec($ch);
            curl_close($ch);
            fwrite($fp,$file);
            fclose($fp);
            return $fullPath;
        }
    }
