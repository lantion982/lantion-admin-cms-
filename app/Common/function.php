<?php
use App\Models\Member;
use App\Models\PlayGroup;
use App\Models\Game;
use App\Models\GameRate;
use App\Models\MemberRate;
use App\Models\GameTime;
use App\Models\GameBet;
use App\Models\ChangLong;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Jobs\HandelSettle;

//数字格式化
function mynumber($intnumber,$dis=0){
	if(!is_numeric($intnumber) || $intnumber == 0){
		return 0;
	}
	if($dis==0){
		$_res = number_format(ROUND($intnumber,2),2,'.','');
	}else{
		$_res = number_format(ROUND($intnumber,2),2);
	}
	return $_res;
}

//获取会员帐号
function getUserName($memberid){
	$username = Member::where("member_id", $memberid)->value("display_name");
	if ($username) {
		return $username;
	}
	return '';
}

//获取客户端IP
if (!function_exists('getip')) {
    function getip($adv = FALSE) {
        $type = 0;
        static $ip = NULL;
        if ($ip !== NULL) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (FALSE !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        //IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array(
            $ip,
            $long
        ) : array(
            '0.0.0.0',
            0
        );

        return $ip[$type];
    }
}

//XML编码
function xml_encode($data, $encoding='utf-8', $root='xml') {
    $xml    = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml   .= '<' . $root . '>';
    $xml   .= data_to_xml($data);
    $xml   .= '</' . $root . '>';
    return $xml;
}

//data_to_xml
function data_to_xml($data) {
    $xml = '';
    foreach ($data as $key => $val) {
        is_numeric($key) && $key = "item id=\"$key\"";
        $xml    .=  "<$key>";
        $xml    .=  ( is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
        list($key, ) = explode(' ', $key);
        $xml    .=  "</$key>";
    }
    return $xml;
}

//获取毫秒时间戳
if (!function_exists('get_microtime')) {
    function get_microtime() {
        $time = explode (" ", microtime () );
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode ( ".", $time );
        $time = $time2 [0];
        $time = (int)$time;
        return  $time;
    }
}

//in Array 自定义
function InArr($num,$arr){
	foreach($arr as $var){
		if(intval($var) == intval($num)) {
			return true;
		}
	}
	return false;
}

//获取玩法分组
function getPlayGroup($game_id){
	$res = PlayGroup::where('game_id',$game_id)->get();
	return $res;
}

//获取玩法分组id
function getGroupId($group_code,$game_id){
	$res = PlayGroup::where('game_id',$game_id)->where('code',$group_code)->value('id');
	return $res;
}

//获取开启的彩种
function getGames(){
	$res = Game::where('is_enable',1)->orderBy('game_sort','asc')->get();
	return $res;
}

//获取彩种名称
function getGameName($game_id){
	$res = Game::where('id',$game_id)->value('game_name');
	return $res;
}

//号码前加0
function formatNum($var){
	$res = $var;
	if(intval($var)<10){
		$res = "0".intval($var);
	}
	return $res;
}

//波色css样式
function getBoseCss($var){
	if(InArr($var,config('enums.BS_red'))){
		$res = 'redBo';
	}elseif(InArr($var,config('enums.BS_blue'))){
		$res = 'blueBo';
	}else{
		$res = 'greenBo';
	}
	return $res;
}

//生成订单号
function getBillNo($gameId){
	$timeTxt = Date('YmdHis');
	$gameArr = Config('enums.GAME_SOLT');
	//$bill_no = $gameArr[$gameId].$timeTxt.chr(mt_rand(65,90)).mt_rand(1000,9999); //加大写字母
	$bill_no = $gameArr[$gameId].$timeTxt.chr(mt_rand(65,90)).mt_rand(1000,9999);
	return $bill_no;
}

//根据Key获取对应的赔率
function getRateByKey($gameId,$panId,$groupCode,$key,$zema_id=0){
	if(($key=='HongBo'||$key=='LiuBo'||$key=='LanBo') && $groupCode!='ZeMa16'){
		$groupCode = 'BoSe';
	}
	$rateData = GameRate::where(['game_id'=>$gameId,'group_code'=>$groupCode,'pan_id'=>$panId])->where(function($query) use($zema_id){
		if($zema_id!=0){
			$query->where('zema_id',$zema_id);
		}
	})->first();
	/*log::info($rateData);
	log::info('【获取赔率】参数：key=>'.$key.'||panid=>'.$panId.'||groupCode=>'.$groupCode.'||gameId=>'.$gameId.'||zema_id=>'.$zema_id);*/
	if(!$rateData){
		log::info('【获取赔率失败】参数：key=>'.$key.'||panid=>'.$panId.'||groupCode=>'.$groupCode.'||gameId=>'.$gameId.'||zema_id=>'.$zema_id);
		return 0;
	}
	$rateArr = json_decode($rateData->rate_data,true);
	//log::info('【getRateByKey】返回key=>'.$key.'||');
	//log::info($rateArr[$key]);
	$rates = $rateArr[$key]??0;
	return $rates;
}

//获取会员返水比例，下注限制
function getWateRate($gruop_code,$member_id,$game_id){
	$rate_info = MemberRate::where('member_id',$member_id)->where('game_id',$game_id)->value('rate_info');
	if(!$rate_info){
		return [];
	}
	$rateArr = json_decode($rate_info,true);
	//Log::info('【获取返水比率'.$member_id.'】'.$rate_info);
	//Log::info($rateArr[$gruop_code]);
	return $rateArr[$gruop_code]??[];
}

//根据盘口取返水比例
function getWaterRateByPid($pid,$rateInfo){
	switch($pid){
		case 1:
			return $rateInfo['paWater']??0;
			break;
		case 2:
			return $rateInfo['pbWater']??0;
			break;
		case 3:
			return $rateInfo['pcWater']??0;
			break;
		case 4:
			return $rateInfo['pdWater']??0;
			break;
		default:
			return $rateInfo['paWater']??0;
			break;
	}
}

//获取会员当期投注额统计
function getSumMoney($member_id,$game_id,$action_number){
	$res = GameBet::where(['member_id'=>$member_id,'game_id'=>$game_id,'action_number'=>$action_number])->sum('action_amount');
	return $res;
}

//更新开奖结果
function updateResult($game_id){
	Log::info('【更新结果数据】=>start:'.$game_id);
	$PlAY_BoSe = [1=>'HongBo',2=>'LiuBo',3=>'LanBo',];
	$redis_arr = ['',"marksix_hongkong","marksix_macao"];
	$redis_key = $redis_arr[$game_id]??'';
	$res       = Redis::get($redis_key);
	$res       = json_decode($res,true);
	$numbers   = $res['data']['current_number']??'';
	$re_code   = $res['data']['current_code']??'';
	$zodiac    = $res['data']['chinese_zodiac']??'';
	$bose      = $res['data']['colors']??[];
	$nextNum   = $res['data']['next_number']??'';
	$nextTime  = $res['data']['next_time']??'';
	$stop_time = date('Y-m-d H:i:s',strtotime("-5 minute",strtotime($nextTime)));
	$star_time = date('Y-m-d H:i:s',strtotime("-6 hours",strtotime($nextTime)));

	foreach($bose as $key =>$item){
		$bose[$key] = $PlAY_BoSe[$item]??'';
	}
	$info['code']   = $re_code;
	$info['zodiac'] = $zodiac;
	$info['bose']   = $bose;
	$info['total']  = $res['data']['sum_total']??'';

	$data['action_number'] = $numbers;
	if($re_code!=''){
		$data['result_code']   = implode(',',$re_code);
	}else{
		$data['result_code']   = '';
	}
	$data['result_json']   = json_encode($info);
	$data['is_close']      = 1;
	$data['updated_at']    = date('Y-m-d H:i:s');
	if(!lhsCheckResult($data['result_code'])){
		Log::info('【更新结果失败】game_id=>'.$game_id."，期号=>".$numbers.'，开奖结果数据不正确！');
		return FAILED;
	}
	$gameTime = GameTime::where(['game_id'=>$game_id,'action_number'=>$numbers])->first();
	if(!$gameTime){
		$data['game_id'] = $game_id;
		$res = GameTime::create($data);
	}elseif($gameTime->result_code!=$data['result_code']&&$gameTime->is_settle==0){
		$res   = GameTime::where(['game_id'=>$game_id,'action_number'=>$numbers])->update($data);
		$clres = updateChangLong($data['result_code'],$game_id);
		$sres  = betSettle($game_id,$numbers);
		Log::info('【更新结果成功】game_id=>'.$game_id."，期号=>".$numbers.'，更新长龙及结算！');
	}
	$newRes = GameTime::where(['game_id'=>$game_id,'action_number'=>$nextNum])->first();
	if($nextNum!=''&&!$newRes){
		$newData['game_id']       = $game_id;
		$newData['action_time']   = $nextTime;
		$newData['action_number'] = $nextNum;
		$newData['start_time']    = $star_time;
		$newData['stop_time']     = $stop_time;
		$newData['is_close']      = 0;
		$newData['is_settle']     = 0;
		$res = GameTime::create($newData);
	}
	Log::info('【更新结果数据】=>end:'.$game_id);
	return SUCCESS;
}

//更新长龙数据
function updateChangLong($res,$gameId){
	$res = explode(',',$res);
	$changLong  = ChangLong::where('game_id',$gameId)->first();
	$dataInfo   = json_decode($changLong->chang_long,true);
	$newData    = [];
	$resum = $i = 0;                                 //总数
	$zema_id    = 1;
	$BS_red     = config('enums.BS_red');
	$BS_blue    = config('enums.BS_blue');
	$BS_green   = config('enums.BS_green');
	foreach($res as $num){
		$temRes  = [];
		$lists   = $dataInfo[$zema_id]??[];
		$hesum   = 0;                                 //合数
		$resum   = $resum+intval($num);               //结果计算
		$val_wei = intval(substr($num,-1));     //尾数
		if(intval($num)<10){
			$hesum = intval($num);
		}else{
			$hesum = intval(substr($num,0,1))+intval(substr($num,-1));
		}
		if($num == 49){
			$temRes['DanShua']    = '和';
			$temRes['DaXiao']     = '和';
			$temRes['HeDanShua']  = '和';
			$temRes['HeDaXiao']   = '和';
			$temRes['DanDaXiao']  = '和';
			$temRes['ShuaDaXiao'] = '和';
			$temRes['WeiDaXiao']  = '和';
		}
		if($num%2!=0&&$num!=49){
			$temRes['DanShua'] = '单';
			if($num>=25){
				$temRes['DanDaXiao'] = '大单';
			}else{
				$temRes['DanDaXiao'] = '小单';
			}
		}elseif($num%2==0&&$num!=49){
			$temRes['DanShua'] = '双';
			if($num>=25){
				$temRes['ShuaDaXiao'] = '大双';
			}else{
				$temRes['ShuaDaXiao'] = '小双';
			}
		}
		if($num>=25&&$num<=48){
			$temRes['DaXiao'] = '大';
		}else{
			$temRes['DaXiao'] = '小';
		}
		if($hesum%2!=0&&$num!=49){
			$temRes['HeDanShua'] = '合单';
		}elseif($hesum%2==0&&$num!=49){
			$temRes['HeDanShua'] = '合双';
		}
		if($hesum>=7&&$num!=49){
			$temRes['HeDaXiao'] = '合大';
		}elseif($hesum<7&&$num!=49){
			$temRes['HeDaXiao'] = '合小';
		}
		//尾数大小
		if($val_wei>=5&&$num!=49){
			$temRes['WeiDaXiao'] = '尾大';
		}elseif($val_wei<5&&$num!=49){
			$temRes['WeiDaXiao'] = '尾小';
		}
		if(in_array($num,$BS_red)){
			$temRes['SeBo'] = '红波';
		}elseif((in_array($num,$BS_blue))){
			$temRes['SeBo'] = '蓝波';
		}elseif(in_array($num,$BS_green)){
			$temRes['SeBo'] = '绿波';
		}

		foreach($temRes as $key=>$val){
			if(array_key_exists($key,$lists)){
				if($lists[$key]['res']==$val){
					$lists[$key]['nums'] += 1;
				}else{
					$lists[$key]['title'] = '正码@正'.$zema_id.$val;
					if($zema_id == 7){
						$lists[$key]['title'] = '特码@'.$val;
					}
					$lists[$key]['res']  = $val;
					$lists[$key]['nums'] = 1;
				}
			}else{
				$lists[$key]['title'] = '正码@正'.$zema_id.$val;
				if($zema_id == 7){
					$lists[$key]['title'] = '特码@'.$val;
				}
				$lists[$key]['res']   = $val;
				$lists[$key]['nums']  = 1;
			}
		}
		$newData[$zema_id] = $lists;
		$zema_id += 1;
	}

	$temRes = [];
	$lists  = $dataInfo[$zema_id]??[];
	if($resum>=175){
		$temRes['ZhongDaXiao'] = '总大';
	}else{
		$temRes['ZhongDaXiao'] = '总小';
	}
	if($resum%2!=0){
		$temRes['ZhongDanShua'] = '总单';
	}else{
		$temRes['ZhongDanShua'] = '总双';
	}
	foreach($temRes as $key=>$val){
		if(array_key_exists($key,$lists)){
			if($lists[$key]['res']==$val){
				$lists[$key]['nums'] += 1;
			}else{
				$lists[$key]['title'] = '总数@'.$val;
				$lists[$key]['res']   = $val;
				$lists[$key]['nums']  = 1;
			}
		}else{
			$lists[$key]['title'] = '总数@'.$val;
			$lists[$key]['res']   = $val;
			$lists[$key]['nums']  = 1;
		}
	}
	$newData[$zema_id] = $lists;
	$changLong->chang_long = json_encode($newData);
	Log::info('【更新长龙信息】');
	$changLong->save();
}

//开奖结果检查
function lhsCheckResult($res){
	$resArr = explode(',',$res);
	$i = 1;
	foreach($resArr as $num){
		if($num<=0 || $num>49){
			return false;
		}
		$i += 1;
	}
	if($i<7) return false;
	return true;
}

//注单结算
function betSettle($game_id,$action_number){
	$gameRes = GameTime::where(['game_id'=>$game_id,'action_number'=>$action_number,'is_close'=>1])->value('result_code');
	if($gameRes==''||empty($gameRes)){
		Log::info('【结算失败】game_id=>'.$game_id."，期号=>".$action_number.'，本期没有开奖结果数据！');
		return false;
	}
	$betList = GameBet::where(['game_id'=>$game_id,'action_number'=>$action_number])->where(function($query){
		$query->where('bet_flag',BET_SUCCESS)->orWhere('bet_flag',BET_FAILE_SETT);
	})->get();
	if($betList->count()<=0){
		Log::info('【结算失败】game_id=>'.$game_id."，期号=>".$action_number.'，本期没有投注注单，跳过！');
		GameTime::where(['game_id'=>$game_id,'action_number'=>$action_number,'is_close'=>1])->update(['is_settle'=>1]);
		return false;
	}
	foreach($betList as $list){
		HandelSettle::dispatch($list->bet_id);                  //加入队列
		log::info('【注单结算加入队列】betId=》'.$list->bet_id);
	}
	try{
		GameTime::where(['game_id'=>$game_id,'action_number'=>$action_number,'is_close'=>1])->update(['is_settle'=>1]);
	}catch(Exception $ex){
		Log::info('【结算失败】game_id=>'.$game_id."，期号=>".$action_number.'，更新本期为已结算异常：'.$ex->getMessage());
	}
	return true;
}

function betSettleCheck($game_id,$action_number){
	$unBet = GameBet::where(['game_id'=>$game_id,'action_number'=>$action_number,'bet_flag'=>0])->count();
	if($unBet>0){
		Log::info('【结算检查】game_id=>'.$game_id."，期号=>".$action_number.'，本期结算未完成，重新结算！');
		return true;
	}
	return false;
}

function betSingn($betinfo){
	$signData = "bill_no={$betinfo->bill_no}&group_code={$betinfo->group_code}&game_id={$betinfo->game_id}&amount={$betinfo->action_amount}";
	$signData = $signData."&rate={$betinfo->action_rate}&key={$betinfo->action_key}&data={$betinfo->action_data}&bet_time={$betinfo->created_at}";
	log::info('【signData】=>'.$signData);
	$signStr  = base64_encode(hash_hmac('sha1',$signData,BET_KEY,true));
	if($signStr!=$betinfo->sign){
		Log::info('【结算验签失败】记录中的签名=》'.$betinfo->sign.'，计算签名：'.$signStr);
		return false;
	}
	return true;
}

//六合彩结算 $res = -1 为和局,$res = 0 输，$res >=1 赢
function lhsSettle($bet_id){
	$res = 0;
	$bet_info = GameBet::where('bet_id',$bet_id)->first();
	if(!$bet_info){
		log::info('【结算失败】未找到bet_id=》'.$bet_id.'的注单');
		return $res;
	}
	$BS_red        = config('enums.BS_red');
	$BS_blue       = config('enums.BS_blue');
	$BS_green      = config('enums.BS_green');
	$group_code    = $bet_info->group_code;
	$game_id       = $bet_info->game_id;
	$zema_id       = $bet_info->zema_id;
	$pan_id        = $bet_info->action_pid;
	$action_number = $bet_info->action_number;
	$action_data   = $bet_info->action_data;
	$action_key    = $bet_info->action_key;
	$action_info   = $bet_info->action_info;
	$info_array    = explode(',',$action_info);
	$result_code   = GameTime::where('game_id',$game_id)->where('action_number',$action_number)->value('result_code');
	$game_result   = explode(',',$result_code);
	$tema_NO       = intval($game_result[6]);
	$SX_Tema       = getSXName($tema_NO);
	$tema_bose     = '';                             //特码波色
	$zeMa_data     = $game_result;
	array_pop($zeMa_data);  //删除数组中的最后一个元素，减去特码
	Log::info('【正码】=>'.json_encode($zeMa_data));
	Log::info('【结果】=>'.$result_code);
	Log::info('【号码】=>'.json_encode($game_result));
	if(in_array($tema_NO,$BS_red)){
		$tema_bose = '红波';
	}elseif(in_array($tema_NO,$BS_blue)){
		$tema_bose = '蓝波';
	}elseif(in_array($tema_NO,$BS_green)){
		$tema_bose = '绿波';
	}
	if(count($game_result)!=7){
		log::info('【结算失败】game_id=》'.$game_id.'，期号=》'.$action_number.'的开奖结果数据或格式不对！');
	}
	$tema_sum = intval($tema_NO);                      //特码合数
	if($tema_NO>=10) $tema_sum = intval(substr($tema_NO,0,1))+intval(substr($tema_NO,-1));
	$val_wei = intval(substr($tema_NO,-1));      //特码尾数
	$resum   = 0;                                      //总数
	foreach($game_result as $var){
		$resum = $resum + intval($var);
	}

	switch($group_code){
		//0、特码
		case 'TeMa':
			if(intval($tema_NO)==intval($action_data)&&intval($action_data)>=1&&intval($action_data)<=49){
				$res = 1;
			}
			return $res;
			break;
		//1、两面
		case 'LMian':
			if($action_data=='特大'){
				if($tema_NO>=25&&$tema_NO<=48){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特小'){
				if($tema_NO>0&&$tema_NO<=24){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特单'){
				if($tema_NO%2!=0&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特双'){
				if($tema_NO%2==0&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特合大'){
				if($tema_sum>=7&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特合小'){
				if($tema_sum<7&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特合单'){
				if($tema_sum%2!=0&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特合双'){
				if($tema_sum%2!=0&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特尾大'){
				if($val_wei>=5&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特尾小'){
				if($val_wei>=0&&$val_wei<5&&$tema_NO!=49){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特大双'){
				if($tema_NO>=25&&$tema_NO<=48&&$tema_NO%2==0){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特小双'){
				if($tema_NO>0&&$tema_NO<=24&&$tema_NO%2==0){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特大单'){
				if($tema_NO>=25&&$tema_NO<=48&&$tema_NO%2!=0){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='特小单'){
				if($tema_NO>0&&$tema_NO<=24&&$tema_NO%2!=0){
					$res = 1;
				}
				if($tema_NO==49){
					$res = -1;
				}
			}elseif($action_data=='总合大'){
				if($resum>=175){
					$res = 1;
				}
			}elseif($action_data=='总合小'){
				if($resum<=174&&$resum>=15){
					$res = 1;
				}
			}elseif($action_data=='总合单'){
				if($resum%2!=0){
					$res = 1;
				}
			}elseif($action_data=='总合双'){
				if($resum%2!=0){
					$res = 1;
				}
			}elseif($action_data=='特天肖'){
				$SX_TIAN = config('enums.SX_TIAN');
				if(in_array($SX_Tema,$SX_TIAN)){
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特地肖'){
				$SX_DI = config('enums.SX_DI');
				if(in_array($SX_Tema,$SX_DI)){
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特前肖'){
				$SX_QIAN = config('enums.SX_QIAN');
				if(in_array($SX_Tema,$SX_QIAN)){
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特后肖'){
				$SX_HOU = config('enums.SX_HOU');
				if(in_array($SX_Tema,$SX_HOU)){
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特家肖'){
				$SX_JIA = config('enums.SX_JIA');
				if(in_array($SX_Tema,$SX_JIA)){
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特野肖'){
				$SX_YE = config('enums.SX_YE');
				if(in_array($SX_Tema,$SX_YE)){
					$res = 1;
				}
				return $res;
			}
			return $res;
			break;
		//2、波色
		case 'BoSe':
			if($action_data==$tema_bose){
				$res = 1;
				return $res;
			}else{
				if($tema_NO==49){
					$res = -1;
					return $res;
				}
			}
			switch($action_data){
				case '红单':
					if(($tema_bose=='红波')&&$tema_NO%2!=0) $res = 1;
					break;
				case '红双':
					if(($tema_bose=='红波')&&$tema_NO%2==0) $res = 1;
					break;
				case '红大':
					if(($tema_bose=='红波')&&$tema_NO>=25&&$tema_NO<=48) $res = 1;
					break;
				case '红小':
					if(($tema_bose=='红波')&&$tema_NO>0&&$tema_NO<=24) $res = 1;
					break;
				case '红小单':
					if(($tema_bose=='红波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2!=0) $res = 1;
					break;
				case '红大单':
					if(($tema_bose=='红波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2!=0) $res = 1;
					break;
				case '红小双':
					if(($tema_bose=='红波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2==0) $res = 1;
					break;
				case '红大双':
					if(($tema_bose=='红波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2==0) $res = 1;
					break;
				case '蓝单':
					if(($tema_bose=='蓝波')&&$tema_NO%2!=0) $res = 1;
					break;
				case '蓝双':
					if(($tema_bose=='蓝波')&&$tema_NO%2==0) $res = 1;
					break;
				case '蓝大':
					if(($tema_bose=='蓝波')&&$tema_NO>=25&&$tema_NO<=48) $res = 1;
					break;
				case '蓝小':
					if(($tema_bose=='蓝波')&&$tema_NO>0&&$tema_NO<=24) $res = 1;
					break;
				case '蓝小单':
					if(($tema_bose=='蓝波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2!=0) $res = 1;
					break;
				case '蓝大单':
					if(($tema_bose=='蓝波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2!=0) $res = 1;
					break;
				case '蓝小双':
					if(($tema_bose=='蓝波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2==0) $res = 1;
					break;
				case '蓝大双':
					if(($tema_bose=='蓝波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2==0) $res = 1;
					break;
				case '绿单':
					if(($tema_bose=='绿波')&&$tema_NO%2!=0) $res = 1;
					break;
				case '绿双':
					if(($tema_bose=='绿波')&&$tema_NO%2==0) $res = 1;
					break;
				case '绿大':
					if(($tema_bose=='绿波')&&$tema_NO>=25&&$tema_NO<=48) $res = 1;
					break;
				case '绿小':
					if(($tema_bose=='绿波')&&$tema_NO>0&&$tema_NO<=24) $res = 1;
					break;
				case '绿小单':
					if(($tema_bose=='绿波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2!=0) $res = 1;
					break;
				case '绿大单':
					if(($tema_bose=='绿波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2!=0) $res = 1;
					break;
				case '绿小双':
					if(($tema_bose=='绿波')&&$tema_NO>0&&$tema_NO<=24&&$tema_NO%2==0) $res = 1;
					break;
				case '绿大双':
					if(($tema_bose=='绿波')&&$tema_NO>=25&&$tema_NO<=48&&$tema_NO%2==0) $res = 1;
					break;
			}
			return $res;
			break;
		//3、特肖
		case 'TeXiao':
			if($action_data==$SX_Tema) $res = 1;
			return $res;
			break;
		//4、合肖
		case 'HeXiao':
			$HeXiaoArr = explode(',',$action_info);
			if($tema_NO!=49&&in_array($SX_Tema,$HeXiaoArr)) $res = 1;
			if($tema_NO==49) $res = -1;
			return $res;
			break;
		//5、特头尾数
		case 'TWS':
			$valtou = substr($tema_NO,0,1).'头';
			$valwei = $val_wei.'尾';
			if($tema_NO<10) $valtou = '0头';
			if($action_data==$valtou||$action_data==$valwei) $res = 1;
			return $res;
			break;
		//6、正码
		case 'ZeMa':
			if(InArr($action_data,$zeMa_data)) $res = 1;
			return $res;
			break;
		//7、正码特
		case 'ZeMaTe':
			$zm_NO    = 0;
			$zm_index = $zema_id-1;
			$zm_NO    = $game_result[$zm_index];
			if(intval($zm_NO)==intval($action_data)&&intval($action_data)>=1&&intval($action_data)<=49) $res = 1;
			return $res;
			break;
		//正码1-6(正码两面)
		case 'ZeMa16':
			$zm_sum = 0;
			$zm_index = $zema_id-1;
			$zm_NO    = $game_result[$zm_index];                                                                         //正码
			$zm_wei   = intval(substr($zm_NO,-1));                                                                 //尾数
			$zm_sum   = intval($zm_NO);                                                                                  //合数
			if($zm_NO>=10) $zm_sum = intval(substr($zm_NO,0,1))+intval(substr($zm_NO,-1));          //合数

			if($zm_NO==49&&($action_data!='红波'&&$action_data!='绿波'&&$action_data!='蓝波')){
				$res = -1;
				return $res;
				break;
			}
			switch($action_data){
				case '单':
					if($zm_NO%2!=0) $res = 1;
					break;
				case '双':
					if($zm_NO%2==0) $res = 1;
					break;
				case '大':
					if($zm_NO>=25&&$zm_NO<=48) $res = 1;
					break;
				case '小':
					if($zm_NO>0&&$zm_NO<=24) $res = 1;
					break;
				case '合单':
					if($zm_sum%2!=0) $res = 1;
					break;
				case '合双':
					if($zm_sum%2!=0&&$zm_sum!=49) $res = 1;
					break;
				case '合大':
					if($zm_sum>=7) $res = 1;
					break;
				case '合小':
					if($zm_sum<7) $res = 1;
					break;
				case '尾大':
					if($zm_wei>=5) $res = 1;
					break;
				case '尾小':
					if($zm_wei>=0&&$zm_wei<5) $res = 1;
					break;
				case '红波':
					if(in_array($zm_sum,$BS_red)) $res = 1;
					break;
				case '绿波':
					if(in_array($zm_sum,$BS_green)) $res = 1;
					break;
				case '蓝波':
					if(in_array($zm_sum,$BS_blue)) $res = 1;
					break;
			}
			return $res;
			break;
		//8、五行
		case 'WuXing':
			$wuxin     = config('enums.WX_Num');
			$wuxin_res = '';
			foreach($wuxin as $key => $val){
				if(in_array($tema_NO,$val)){
					$wuxin_res = $key;
					break;
				}
			}
			if($action_data == $wuxin_res && $action_data!=''){
				$res = 1;
			}
			return $res;
			break;
		//9、平特一肖，平码+特码生肖，中一肖即中奖
		case 'PTYiXiao':
			foreach($game_result as $var){
				$ptSX_name = getSXName($var);
				if($ptSX_name==$action_data) $res = 1;
				break;   //中一肖即为中奖，退出循环
			}
			return $res;
			break;
		//10、平特尾数，平码+特码尾数
		case 'PTWeiSu':
			foreach($game_result as $var){
				$ptSX_wei = substr($var,-1).'尾';
				if($ptSX_wei==$action_data) $res = 1;       //中一尾即为中奖，退出循环
				break;
			}
			return $res;
			break;
		//11、正肖，仅算正码的生肖
		case 'ZeXiao':
			$zeMa_data = $game_result;
			array_pop($zeMa_data);           //删除数组中的最后一个元素，减去特码
			foreach($zeMa_data as $var){
				$SeX_name = getSXName($var);
				if($SeX_name==$action_data){
					$res = $res+1;                  //每中一肖，中奖翻倍
				}
			}
			return $res;
			break;
		//12、总肖
		case 'ZongXiao':
			$BS_array = [];
			foreach($game_result as $k => $var){
				$BS_array[$k] = getSXName($var);
			}
			$BS_array = array_unique($BS_array);
			$BS_count = count($BS_array);
			if($action_data=='总肖单'){
				if($BS_count%2!=0) $res = 1;
			}elseif($action_data=='总肖双'){
				if($BS_count%2==0) $res = 1;
			}else{
				if($BS_count == $action_key) $res = 1;
			}
			return $res;
			break;
		//13、自选不中
		case 'ZXBZ':
			$action_arr = explode(',',$action_data);
			foreach($action_arr as $var){
				if(InArr($var,$game_result)){
					$res = 0;
					return $res;
				}
			}
			$res = 1;
			return $res;
			break;
		//14、连肖
		case 'LianXiao':
			$SX_name = '';
			$lxcount = 0;
			$clx     = 0;
			$tem_info = $info_array;
			switch($action_data){
				case '二肖':
					$clx = 2;
					break;
				case '三肖':
					$clx = 3;
					break;
				case '四肖':
					$clx = 4;
					break;
				case '五肖':
					$clx = 5;
					break;
			}
			foreach($game_result as $var){
				$SX_name = getSXName($var);
				foreach($tem_info as $key =>$infos){
					if($infos==$SX_name){
						unset($tem_info[$key]);
						$lxcount = $lxcount+1;
						break;
					}
				}
				if($lxcount>=$clx) break;
			}
			if($lxcount==$clx) $res = 1;
			return $res;
			break;
		//15、连尾
		case 'LianWei':
			$tem_wei  = '';
			$lwcount  = 0;
			$clx      = 0;
			$tem_info = $info_array;
			switch($action_data){
				case '二尾':
					$clx = 2;
					break;
				case '三尾':
					$clx = 3;
					break;
				case '四尾':
					$clx = 4;
					break;
				case '五尾':
					$clx = 5;
					break;
			}
			foreach($game_result as $var){
				$tem_wei = substr($var,-1).'尾';
				foreach($tem_info as $key=>$infos){
					if($infos==$tem_wei){
						unset($tem_info[$key]);
						$lwcount = $lwcount+1;
						break;
					}
				}
				if($lwcount>=$clx) break;
			}
			if($lwcount>=$clx) $res = 1;
			return $res;
			break;
		//16、连码
		case 'LianMa':
			if($action_data=='四全中'){
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=5;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==4) $res = 1;
				return $res;
			}elseif($action_data=='三全中'){                    //三个都是正码
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=5;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==3) $res = 1;
				return $res;
			}elseif($action_data=='三中二'){                    //三个都是正码
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=5;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==2){
					$gcode   = '3z2z2';
					$newRate = getRateByKey($game_id,$pan_id,$group_code,$gcode);
					$bet_info->action_rate = $newRate;              //更新为中二赔率
					$bet_info->save();
					$res = 1;
				}elseif($wintimes==3){
					$gcode   = '3z2z3';
					$newRate = getRateByKey($game_id,$pan_id,$group_code,$gcode);
					$bet_info->action_rate = $newRate;              //更新为中三赔率
					$bet_info->save();
					$res = 1;
				}
				return $res;
			}elseif($action_data=='二全中'){
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=5;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==2) $res = 1;
				return $res;
			}elseif($action_data=='二中特'){
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=6;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==2&&InArr($tema_NO,$info_array)){
					$gcode = '2ztzt';
					$newRate = getRateByKey($game_id,$pan_id,$group_code,$gcode);
					$bet_info->action_rate = $newRate;              //更新为中特赔率
					$bet_info->save();
					$res = 1;
				}elseif($wintimes==2){
					$gcode = '2ztz2';
					$newRate = getRateByKey($game_id,$pan_id,$group_code,$gcode);
					$bet_info->action_rate = $newRate;              //更新为中二赔率
					$bet_info->save();
					$res = 1;
				}
				return $res;
			}elseif($action_data=='特串'){
				$wintimes = 0;
				foreach($info_array as $var){
					for($i = 0;$i<=6;$i++){
						if(intval($var)==intval($game_result[$i])){
							$wintimes++;
						}
					}
				}
				if($wintimes==2&&InArr($tema_NO,$info_array)) $res = 1;
				return $res;
			}
			break;
		//17、7色波
		case 'QiSeBo':
			$red_count = $blue_count = $green_count = 0;
			foreach($game_result as $k => $var){
				if(InArr(intval($var),$BS_red)){
					if($k==6){
						$red_count = $red_count+1.5;
					}else{
						$red_count = $red_count+1;
					}
				}elseif(InArr(intval($var),$BS_blue)){
					if($k==6){
						$blue_count = $blue_count+1.5;
					}else{
						$blue_count = $blue_count+1;
					}
				}elseif(InArr(intval($var),$BS_green)){
					if($k==6){
						$green_count = $green_count+1.5;
					}else{
						$green_count = $green_count+1;
					}
				}
			}
			switch($action_data){
				case '红波':
					if($red_count>$blue_count&&$red_count>$green_count) $res = 1;
					break;
				case '蓝波':
					if($blue_count>$red_count&&$blue_count>$green_count) $res = 1;
					break;
				case '绿波':
					if($green_count>$red_count&&$green_count>$blue_count) $res = 1;
					break;
				case '和局':
					if(($red_count==3&&$red_count==$blue_count)||($blue_count==3&&$blue_count==$green_count)||($green_count==3&&$green_count==$red_count)){
						$res = 1;
					}
					break;
			}
			return $res;
			break;
	}
	return $res;
}

//获取本年生肖排序号
function getSXIndex(){
	$SX_Year  = config('enums.SX_Year');
	$SX_Array = config('enums.SX_Array');
	$i = 0;
	foreach($SX_Array as $var){
		if($var==$SX_Year){
			return $i;
		}
		$i += 1;
	}
	return null;
}

//获取生肖对应的号码组号
function getSXNumIndex($SX_Name){
	$SX_index   = getSXIndex();
	$SX_Array   = config('enums.SX_Array');
	$SXNumIndex = 1;
	for($i = $SX_index;$i>=0;$i--){
		//echo('$SXNumIndex====>'.$SXNumIndex.'||$I==>'.$i.'<br>');
		if($SX_Array[$i]==$SX_Name){
			return $SXNumIndex;
			//echo($SX_Name.'====>'.$SXNumIndex.'<br>++++++<br>');
			break;
		}
		$SXNumIndex = $SXNumIndex+1;
	}
	for($k = count($SX_Array)-1;$k>$SX_index;$k--){
		$temName = $SX_Array[$k]??'';
		//echo('$SXNumIndex====>'.$SXNumIndex.'||$k==>'.$k.'||temName=>'.$temName.'<br>');
		if($temName==$SX_Name){
			return $SXNumIndex;
			//echo($SX_Name.'--=>'.$SXNumIndex.'<br>---<br>');
			break;
		}
		$SXNumIndex = $SXNumIndex+1;
	}

	//return $SXNumIndex;
}

//按号码获取生肖
function getSXName($num){
	$SX_Array = config('enums.SX_Array');
	$SX_NUMS  = config('enums.SX_NUMS');
	$SX_Name  = '';
	foreach($SX_Array as $var){
		//echo('<br><br>call getSXNumIndex=>'.$var);
		$temSXNum = $SX_NUMS[getSXNumIndex($var)]??'';
		if(InArr($num,$temSXNum)){
			$SX_Name = $var;
			break;
		}
	}
	return $SX_Name;
}