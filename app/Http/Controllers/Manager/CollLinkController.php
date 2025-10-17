<?php
    //后台帐号|权限
    namespace App\Http\Controllers\Manager;
    use App\Models\Down;
    use App\Models\Link;
    use Illuminate\Http\Request;
    use QL\QueryList;
    
    class CollLinkController extends Controller{
    
        public function index(Request $request){
            $rules = [
                'title'   => ['h3','text'],
                'link'    => ['a','href'],
                'icon'    => ['img','src'],
                'remarks' => ['p','text']
            ];
            $range = '.item';
            $data  = QueryList::get('https://hao.uisdc.com/')->rules($rules)->range($range)->query()->getData();
            foreach($data as $key =>$var){
                $icon      = $var['icon'];
                $fullPath = $this->savePic($icon,'link',$key);
                $var['icon']  = $fullPath;
                $var['sorts'] = $key;
                $res = Link::create($var);
            }
        }
    
        public function zkDown(Request $request){
            set_time_limit(0);  //设置为0，表示无限制。
            ob_end_clean();             //在输出前，要关闭输出缓冲区
            $page  = $request->input('page',1);
            //$url   = 'https://www.zztuku.com/source/'.$page.'.html';
            $url   = 'https://www.zztuku.com/theme/'.$page.'.html';
            $rules = [
                'title'   => ['h2','text'],
                'link'    => ['a','href'],
                'pic'     => ['img','data-original'],
            ];
            $range = '.article-card>article';
            $data  = QueryList::get($url)->rules($rules)->range($range)->query()->getData();
            echo "开始采集列表=》第【<span style='color:red'>".$page."</span>】页<br>";
           
            foreach($data as $key =>$val){
                $downs = Down::where('title',$val['title'])->count();
                if($downs>0){
                    echo "开始采集数据=》第【<span style='color:red'>".($key+1)."</span>】条信息已存，跳过<br>";
                    continue;
                }
                echo "开始采集数据=》第【<span style='color:red'>".($key+1)."</span>】条信息<br>";
                flush();
                $pageUrl = $val['link'];
                $pic = 'https://www.zztuku.com'.$val['pic'];
                $localPic = $this->savePic($pic,'theme',$key);
                $pageRule = ['content' => ['.show-content','html','a strong'],];
                $ql = QueryList::get($pageUrl)->rules($pageRule)->query()->getData();
                $imgs = QueryList::html($ql['content'])->find('img')->attrs('src')->all();
                $text = QueryList::html($ql['content'])->find('')->texts();
                $content = '<p>'.$text[0].'</p><br/>';
                foreach($imgs as $pkey => $img){
                    $lpic = $this->savePic('https://www.zztuku.com'.$img,'theme',$pkey);
                    $content = $content."<p><img src='$lpic' alt='图片介绍'></p>";
                }
                $val['content'] = $content;
                $val['is_show'] = 0;
                $val['cid']     = 2;
                $val['pic']     = $localPic;
                unset($val['link']);
                $res = Down::create($val);
            }
            $page += 1;
            return redirect("/manager/zkDown?page=".$page);
        }
    
        public function zkNews(Request $request){
            set_time_limit(0);  //设置为0，表示无限制。
            ob_end_clean();             //在输出前，要关闭输出缓冲区
            $page  = $request->input('page',1);
            $url   = 'https://www.zztuku.com/source/'.$page.'.html';
            $rules = [
                'title'   => ['h2','text'],
                'link'    => ['a','href'],
                'pic'     => ['img','data-original'],
            ];
            $range = '.article-card>article';
            $data  = QueryList::get($url)->rules($rules)->range($range)->query()->getData();
            echo "开始采集列表=》第【<span style='color:red'>".$page."</span>】页<br>";
        
            foreach($data as $key =>$val){
                $downs = Down::where('title',$val['title'])->count();
                if($downs>0){
                    //echo "开始采集数据=》第【<span style='color:red'>".($key+1)."</span>】条信息已存，跳过<br>";
                    continue;
                }
                echo "开始采集数据=》第【<span style='color:red'>".($key+1)."</span>】条信息<br>";
                flush();
                $pageUrl = $val['link'];
                $pic = 'https://www.zztuku.com'.$val['pic'];
                $localPic = $this->savePic($pic,'down',$key);
            
                $pageRule = ['content' => ['.show-content','html','a strong'],];
                $ql = QueryList::get($pageUrl)->rules($pageRule)->query()->getData();
                $imgs = QueryList::html($ql['content'])->find('img')->attrs('src')->all();
                $text = QueryList::html($ql['content'])->find('')->texts();
                $content = '<p>'.$text[0].'</p><br/>';
                foreach($imgs as $pkey => $img){
                    $lpic = $this->savePic('https://www.zztuku.com'.$img,'down',$pkey);
                    $content = $content."<p><img src='$lpic' alt='图片介绍'></p>";
                }
                $val['content'] = $content;
                $val['is_show'] = 0;
                $val['pic']     = $localPic;
                unset($val['link']);
                $res = Down::create($val);
            }
            $page += 1;
            return redirect("/manager/zkNews?page=".$page);
        }
    
    
        public function savePic($url,$path,$key=0){
            $parse_url = parse_url($url);
            $pathinfo  = pathinfo($parse_url['path']);
            $fileName  = 'pic'.date('mdHis').$key.'.'.$pathinfo['extension']??'png';
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
