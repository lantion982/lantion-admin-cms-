<?php
/*
|--------------------------------------------------------------------------
| 游戏相关API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use App\Jobs\HandelBet;
use App\Models\GameBet;
use App\Models\GameRate;
use App\Models\GameTime;
use App\Models\Member;
use App\Models\PlayGroup;
use App\Models\QueueBet;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends BaseController{
	protected $memberId;
	protected $member;
	protected $PlayArr;
	public function __construct(){
		$this->middleware(function($request,$next){
			$this->member   = $request->user();
			$this->memberId = $this->member->member_id;
			if(!empty($this->member)){
				return $next($request);
			}else{
				return response()->json(['status'=>FAILED,'msg'=>'无法获取您的账户信息，请重新登录！']);
			}
		});
		$this->PlayArr["LMian"]    = config('enums.PLAY_LMian');
		$this->PlayArr["BoSe"]     = config('enums.PLAY_BoSe');
		$this->PlayArr["ZeMa16"]   = config('enums.PLAY_ZeMa16');
		$this->PlayArr["WuXing"]   = config('enums.PLAY_WuXing');
		$this->PlayArr["QiSeBo"]   = config('enums.PLAY_QiSeBo');
		$this->PlayArr["ZongXiao"] = config('enums.PLAY_ZongXiao');
		$this->PlayArr["TeXiao"]   = config('enums.PLAY_SeXiao');
		$this->PlayArr["ZeXiao"]   = config('enums.PLAY_SeXiao');
		$this->PlayArr["PTYiXiao"] = config('enums.PLAY_SeXiao');
		$this->PlayArr["ZeMa"]     = [];
		for($i=1;$i<=49;$i++){
			$this->PlayArr["ZeMa"][$i] = $i;
			if($i<10){
				$this->PlayArr["ZeMa"][$i] = '0'.$i;
			}
		}
		for($k=0;$k<=9;$k++){
			$this->PlayArr["PTWeiSu"][$k] = $k.'尾';
		}
		$this->PlayArr["ZXBZ"]    = $this->PlayArr["ZeMa"];
		$this->PlayArr["LianMa"]  = $this->PlayArr["ZeMa"];
		$this->PlayArr["TeMa"]    = $this->PlayArr["ZeMa"];
		$this->PlayArr["LianWei"] = $this->PlayArr["PTWeiSu"];
	}

	//获取本期开盘封盘时间等基本信息
	public function getGameBase(Request $request){
		$gameId    = intval($request->input('gameId',2));
		$gameTime  = GameTime::where('game_id',$gameId)->where('is_close',0)->orderBy('action_number','asc')->first();          //本期
		$preData   = GameTime::where('game_id',$gameId)->where('is_close',1)->orderBy('action_number','desc')->first();         //上期
		$data      = [
			'closeTime'=>'0','openTime'=>'0','startTime'=>'0','actionNumber'=>'','preNumber'=>$preData->action_number??'',
			'preResult'=>$preData->result_code??'','isClose'=>true,'isOpen'=>false,'nowTime'=>date('Y-m-d H:i:s')
		];
		
		if(!$gameTime&&$gameId==2){
			$newData['game_id']       = $gameId;
			$newData['action_time']   = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($preData->action_time)));
			$newData['action_number'] = $preData->action_number+1;
			$newData['start_time']    = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($preData->start_time)));;
			$newData['stop_time']     = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($preData->stop_time)));;
			$newData['is_close']      = 0;
			$newData['is_settle']     = 0;
			$gameTime = GameTime::create($newData);

		}
	
		$data['actionNumber'] = $gameTime->action_number;
		$closeTime  = strtotime($gameTime->stop_time)-time();
		$startTime  = strtotime($gameTime->start_time)-strtotime(date('Y-m-d H:i:s'));
		$openTime   = strtotime($gameTime->action_time)-strtotime(date('Y-m-d H:i:s'));
		$data['closeTime']   = $closeTime;
		$data['openTime']    = $openTime;
		$data['startTime']   = $startTime;
		$data['start_time']  = $gameTime->start_time;
		$data['action_time'] = $gameTime->action_time;
		$data['stop_time']   = $gameTime->stop_time;
		$data['now_time']    = time();
		if($startTime>0){
			return response()->json(['status'=>SUCCESS,'msg'=>'本期未开盘！','content'=>$data]);
		}
		if($closeTime<=0){
			$gameTime->is_close = 1;
			$gameTime->save();
			$data['closeTime'] = 0;
			$data['openTime']  = 0;
			$data['startTime'] = 0;
			return response()->json(['status'=>SUCCESS,'msg'=>'本期已封盘！','content'=>$data]);
		}
		$data['isClose']     = false;
		$data['isOpen']      = true;

		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$data]);
	}

	//通用获取赔率
	public function getGameRate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$groupCode  = intval($request->input('groupCode',''));
		$rateData   = GameRate::where('game_id',$gameId)->where('group_code',$groupCode)->where('pan_id',$panId)->first();
		if(!$rateData){
			return response()->json(['status'=>FAILED,'msg'=>'未找到对应赔率信息！','data'=>'']);
		}
		$data['rateData'] = json_decode($rateData->rate_data,true);
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$data]);
	}

	//获取上期开奖结果
	public function getBetResult(Request $request){
		$gameId  = intval($request->input('gameId',2));
		$gameRes = GameTime::where('game_id',$gameId)->where('is_close',1)->orderBy('action_number','desc')->first();        //上期开奖结果
		
		if($gameRes){
			$data['opening'] = 0;
			$betResult = explode(',',$gameRes->result_code);
			$resZeMa = [];
			$resTeMa = [];
			if(empty($gameRes->result_code)||$gameRes->result_code=='') $data['opening'] = 1;
			foreach($betResult as $key =>$var){
				if($key<6){
					$resZeMa[$key]['numbers'] = $var;
					$resZeMa[$key]['ZeXiao']  = getSXName($var);
					$resZeMa[$key]['cssName'] = 'b'.$var;
				}else{
					$resTeMa['numbers'] = $var;
					$resTeMa['TeXiao']  = getSXName($var);
					$resTeMa['cssName'] = 'b'.$var;
				}
			}
			$data['resZeMa']      = $resZeMa ;
			$data['resTeMa']      = $resTeMa ;
			$data['actionNumber'] = $gameRes->action_number;
			return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$data]);
		}

		return response()->json(['status'=>FAILED,'msg'=>'success','content'=>'']);
	}

	//获取会员最新投注
	public function getNewBetList(Request $request){
		$list = GameBet::where('member_id',$this->memberId)->where('bet_flag',BET_SUCCESS)->orderBy('created_at','desc')->limit(10)
			->select('game_id','bill_no','action_number','group_name','action_data','action_info','action_rate','action_amount','created_at')->get();
		$gameArr = ['--','港彩','澳彩'];

		foreach($list as $var){
			$var->game_name = $gameArr[$var->game_id]??'-';
		}
		$data['betList']  = $list;
		$data['betCount'] = $list->count();
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$data]);
	}
	
	//特码号码排列及赔率数据
	public function getTeMaRate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','TeMa')->where('pan_id',$panId)->first();
		$getRate2   = GameRate::where('game_id',$gameId)->where('group_code','LMian')->where('pan_id',$panId)->first();
		$getRate3   = GameRate::where('game_id',$gameId)->where('group_code','BoSe')->where('pan_id',$panId)->first();
		$isEnable1  = PlayGroup::where('code','TeMa')->value('is_enable');
		$isEnable2  = PlayGroup::where('code','LMian')->value('is_enable');
		$isEnable3  = PlayGroup::where('code','BoSe')->value('is_enable');
		$LMianArr1  = ['TeDan'=>'特单','TeDa'=>'特大','TeHeDan'=>'特合单','TeHeDa'=>'特合大','TeWeiDa'=>'特尾大',];
		$LMianArr2  = ['TeShua'=>'特双','TeXiao'=>'特小','TeHeShua'=>'特合双','TeHeXiao'=>'特合小','TeWeiXiao'=>'特尾小',];
		$BoSeArr    = ['HongBo'=>'红波','LanBo'=>'蓝波','LiuBo'=>'绿波',];
		$playArr1   = array_merge($LMianArr1,$LMianArr2,$BoSeArr);
		$rateData1  = $rateData2 = $rateData3 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1  = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}
		if($getRate3&&$isEnable3==1){
			$rateData3  = json_decode($getRate3->rate_data,true);
		}
		$k = 0;
		$listItem1  = $listItem2 = [];
		for($i=1;$i<=10;$i++){
			for($j=0;$j<=4;$j++){
				$temi = $i+10*$j;
				if($temi<50){
					$listItem1[$k][$j]['key']  = $temi;
					$css = $temi<10?'0':'';
					$listItem1[$k][$j]['ball'] = 'b'.$css.$temi;
					$listItem1[$k][$j]['rate'] = $rateData1[$temi]??'--';
				}
			}
			$k += 1;
		}
		
		$k = 0;
		foreach($LMianArr1 as $key =>$var){
			$listItem2[0][$k]['key']  = $key;
			$listItem2[0][$k]['ball'] = $var;
			$listItem2[0][$k]['rate'] = $rateData2[$key]??'--';
			$k += 1;
		}
		$k = 0;
		foreach($LMianArr2 as $key =>$var){
			$listItem2[1][$k]['key']  = $key;
			$listItem2[1][$k]['ball'] = $var;
			$listItem2[1][$k]['rate'] = $rateData2[$key]??'--';
			$k += 1;
		}
		$k = 0;
		foreach($BoSeArr as $key =>$var){
			$listItem2[2][$k]['key']  = $key;
			$listItem2[2][$k]['ball'] = $var;
			$listItem2[2][$k]['rate'] = $rateData3[$key]??'--';
			$k += 1;
		}

		$res['listItem1'] = $listItem1;
		$res['listItem2'] = $listItem2;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = array_merge($rateData2,$rateData3);
		$res['playArr1']  = $playArr1;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}
	
	//获取玩法及赔率数据
	public function getRateByGroup(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$zemaId     = $request->input('zemaId','0');
		$groupCode  = $request->input('groupCode','LMian');
		$clums      = intval($request->input('clums',2));
		$getRate1   = GameRate::where(['game_id'=>$gameId,'group_code'=>$groupCode,'pan_id'=>$panId])->where(function($query) use($zemaId){
			if($zemaId!='0'){
				$query->where('zema_id',$zemaId);
			}
		})->first();
		$isEnable1  = PlayGroup::where('code',$groupCode)->value('is_enable');
		$playArr1   = $this->PlayArr[$groupCode];
		
		$rateData1 = $listItem1 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		
		$k = $i = 0;
		foreach($playArr1 as $key =>$var){
			if($clums==99){
				$listItem1[$k]['key']  = $key;
				$listItem1[$k]['ball'] = $var;
				$listItem1[$k]['rate'] = $rateData1[$key]??'--';
				$k += 1;
			}else{
				$listItem1[$i][$k]['key']  = $key;
				$listItem1[$i][$k]['ball'] = $var;
				$listItem1[$i][$k]['rate'] = $rateData1[$key]??'--';
				if(($k+1)%$clums==0){
					$i += 1;
					$k = 0;
				}else{
					$k += 1;
				}
			}
		}
		$res['listItem1'] = $listItem1;
		$res['rateData1'] = $rateData1;
		$res['playArr1']  = $playArr1;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}
	
	//特肖头尾玩法排列及赔率数据
	public function getTeXiaoRate(Request $request){
		$clums     = intval($request->input('clums',2));
		$gameId    = intval($request->input('gameId',2));
		$panId     = intval($request->input('panId',1));
		$getRate1  = GameRate::where('game_id',$gameId)->where('group_code','TeXiao')->where('pan_id',$panId)->first();
		$getRate2  = GameRate::where('game_id',$gameId)->where('group_code','TWS')->where('pan_id',$panId)->first();
		$isEnable1 = PlayGroup::where('code','TeXiao')->value('is_enable');
		$isEnable2 = PlayGroup::where('code','TWS')->value('is_enable');
		$playArr1  = config('enums.PLAY_SeXiao');
		$playArr2  = config('enums.PLAY_TWS');
		$SX_Nums   = config('enums.SX_NUMS');
		
		$rateData1 = $rateData2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}
		
		$listItem1  = $listItem2 = [];
		$k = $i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i][$k]['key']  = $key;
			$listItem1[$i][$k]['name'] = $var;
			$listItem1[$i][$k]['ball'] = $SX_Nums[$key];
			$listItem1[$i][$k]['rate'] = $rateData1[$key]??'--';
			if(($k+1)%2==0){
				$i += 1;
				$k = 0;
			}else{
				$k += 1;
			}
		}
		if($clums==99){
			for($j=0;$j<=9;$j++){
				$listItem2[$j]['key']  = 'w'.$j;
				$listItem2[$j]['name'] = $j.'尾';
				$listItem2[$j]['rate'] = $rateData2['w'.$j]??'--';
			}
			$i = 10;
			for($j=0;$j<=4;$j++){
				$listItem2[$i+$j]['key']  = 't'.$j;
				$listItem2[$i+$j]['name'] = $j.'头';
				$listItem2[$i+$j]['rate'] = $rateData2['t'.$j]??'--';
			}
		}else{
			for($j=0;$j<=4;$j++){
				$listItem2[$j][0]['key']  = 'w'.$j;
				$listItem2[$j][0]['name'] = $j.'尾';
				$listItem2[$j][0]['rate'] = $rateData2['w'.$j]??'--';
				
				$listItem2[$j][1]['key']  = 'w'.($j+5);
				$listItem2[$j][1]['name'] = ($j+5).'尾';
				$listItem2[$j][1]['rate'] = $rateData2['w'.($j+5)]??'--';
				
				$listItem2[$j][2]['key']  = 't'.$j;
				$listItem2[$j][2]['name'] = $j.'头';
				$listItem2[$j][2]['rate'] = $rateData2['t'.$j]??'--';
			}
		}
		$res['listItem1'] = $listItem1;
		$res['listItem2'] = $listItem2;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = $rateData2;
		$res['playArr1']  = $playArr1;
		$res['playArr2']  = $playArr2;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}
	
	//合肖玩法排列及赔率数据
	public function getHeXiaoRate(Request $request){
		$clums      = intval($request->input('clums',2));
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','HeXiao')->where('pan_id',$panId)->first();
		$isEnable1  = PlayGroup::where('code','HeXiao')->value('is_enable');
		$playArr1   = config('enums.PLAY_HeXiao');
		$playArr2   = config('enums.SX_Array');
		$SX_Nums    = config('enums.SX_NUMS');
		$rateData1  = $listItem1 = $listItem2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
	
		$i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i]['key']  = $key;
			$listItem1[$i]['name'] = $var;
			$listItem1[$i]['rate'] = $rateData1[$key]??'--';
			$i += 1;
		}
		$k = $i = 0;
		foreach($playArr2 as $key =>$var){
			if($clums==99){
				$listItem2[$k]['key']  = $key;
				$listItem2[$k]['name'] = $var;
				$listItem2[$k]['ball'] = $SX_Nums[getSXNumIndex($var)]??[];
				$k +=1;
			}else{
				$listItem2[$i][$k]['key']  = $key;
				$listItem2[$i][$k]['name'] = $var;
				$listItem2[$i][$k]['ball'] = $SX_Nums[getSXNumIndex($var)]??[];
				$k +=1;
				if($k==2){
					$k = 0;
					$i +=1;
				}
			}
		}

		$res['rateData1']  = $rateData1;
		$res['listItem1']  = $listItem1;
		$res['listItem2']  = $listItem2;
		$res['playArr1']   = $playArr1;
		$res['playArr2']   = $playArr2;

		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//平特一肖尾数玩法排列及赔率数据
	public function getPTYXWSRate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','PTYiXiao')->where('pan_id',$panId)->first();
		$getRate2   = GameRate::where('game_id',$gameId)->where('group_code','PTWeiSu')->where('pan_id',$panId)->first();
		$isEnable1  = PlayGroup::where('code','PTYiXiao')->value('is_enable');
		$isEnable2  = PlayGroup::where('code','PTWeiSu')->value('is_enable');
		$playArr1   = config('enums.SX_Array');
		$playArr2   = [];
		$SX_Nums    = config('enums.SX_NUMS');

		$rateData1 = $rateData2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}

		$listItem1  = $listItem2 = [];
		$k = $i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i][$k]['key']  = $key+1;
			$listItem1[$i][$k]['name'] = $var;
			$listItem1[$i][$k]['ball'] = $SX_Nums[$key+1];
			$listItem1[$i][$k]['rate'] = $rateData1[$key+1]??'--';
			if(($k+1)%2==0){
				$i += 1;
				$k = 0;
			}else{
				$k += 1;
			}
		}
		for($j=0;$j<=4;$j++){
			$listItem2[$j][0]['key']  = $j;
			$listItem2[$j][0]['name'] = $j.'尾';
			$playArr2[$j]=$j.'尾';
			for($b=0;$b<=4;$b++){
				$ball = $j+$b*10;
				if($ball>0) $listItem2[$j][0]['ball'][$b] = $ball;
			}
			$listItem2[$j][0]['rate'] = $rateData2[$j]??'--';

			$listItem2[$j][1]['key']  = ($j+5);
			$listItem2[$j][1]['name'] = ($j+5).'尾';
			$playArr2[$j+5]=($j+5).'尾';
			for($b=0;$b<=4;$b++){
				$ball = $b*10+$j+5;
				$listItem2[$j][1]['ball'][$b] = $ball;
			}
			$listItem2[$j][1]['rate'] = $rateData2[($j+5)]??'--';
		}

		$res['listItem1'] = $listItem1;
		$res['listItem2'] = $listItem2;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = $rateData2;
		$res['playArr1']  = $playArr1;
		$res['playArr2']  = $playArr2;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//正码号码排列及赔率数据
	public function getZeMaTeRate(Request $request){
		$clums      = intval($request->input('clums',2));
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$zemaId     = $request->input('zemaId',1);
		$getRate1   = GameRate::where(['game_id'=>$gameId,'group_code'=>'ZeMaTe','pan_id'=>$panId,'zema_id'=>$zemaId])->first();
		$getRate2   = GameRate::where(['game_id'=>$gameId,'group_code'=>'ZeMa16','pan_id'=>$panId,'zema_id'=>$zemaId])->first();
		$ZM16Arr1   = ['ZeDan'=>'单','ZeDa'=>'大','HeDan'=>'合单','HeDa'=>'合大','WeiDa'=>'尾大'];
		$ZM16Arr2   = ['ZeShua'=>'双','ZeXiao'=>'小','HeShua'=>'合双','HeXiao'=>'合小','WeiXiao'=>'尾小'];
		$ZM16Arr3   = ['HongBo'=>'红波','LanBo'=>'蓝波','LiuBo'=>'绿波'];
		$playArr1   = array_merge($ZM16Arr1,$ZM16Arr2,$ZM16Arr3);
		$isEnable1  = PlayGroup::where('code','ZeMaTe')->value('is_enable');
		$isEnable2  = PlayGroup::where('code','ZeMa16')->value('is_enable');
		$rateData1  = $rateData2 =[];

		if($getRate1&&$isEnable1==1){
			$rateData1  = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}

		$k = 0;
		$listItem1  = $listItem2 = [];
		if($clums==99){
			foreach($this->PlayArr["ZeMa"] as $key=>$var){
				$listItem1[$key]['key']  = $key;
				$listItem1[$key]['ball'] = $var;
				$listItem1[$key]['css']  = 'b'.$var;
				$listItem1[$key]['rate'] = $rateData1[$key]??'--';
			}
		}else{
			for($i=1;$i<=10;$i++){
				for($j=0;$j<=4;$j++){
					$temi = $i+10*$j;
					if($temi<50){
						$listItem1[$k][$j]['key']  = $temi;
						$css = $temi<10?'0':'';
						$listItem1[$k][$j]['ball'] = 'b'.$css.$temi;
						$listItem1[$k][$j]['rate'] = $rateData1[$temi]??'--';
					}
				}
				$k += 1;
			}
		}
		$k = 0;
		foreach($ZM16Arr1 as $key =>$var){
			$listItem2[0][$k]['key']  = $key;
			$listItem2[0][$k]['ball'] = $var;
			$listItem2[0][$k]['rate'] = $rateData2[$key]??'--';
			$k += 1;
		}
		$k = 0;
		foreach($ZM16Arr2 as $key =>$var){
			$listItem2[1][$k]['key']  = $key;
			$listItem2[1][$k]['ball'] = $var;
			$listItem2[1][$k]['rate'] = $rateData2[$key]??'--';
			$k += 1;
		}
		$k = 0;
		foreach($ZM16Arr3 as $key =>$var){
			$listItem2[1][$k]['key']  = $key;
			$listItem2[1][$k]['ball'] = $var;
			$listItem2[1][$k]['rate'] = $rateData2[$key]??'--';
			$k += 1;
		}

		$res['listItem1'] = $listItem1;
		$res['listItem2'] = $listItem2;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = $rateData2;
		$res['playArr1']  = $playArr1;

		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//五行7色波排列及赔率数据
	public function getWuXingRate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','WuXing')->where('pan_id',$panId)->first();
		$getRate2   = GameRate::where('game_id',$gameId)->where('group_code','QiSeBo')->where('pan_id',$panId)->first();
		$playArr1   = config('enums.PLAY_WuXing');
		$playArr2   = config('enums.PLAY_QiSeBo');
		$wxNumsArr  = config('enums.WX_Num');
		$isEnable1  = PlayGroup::where('code','WuXing')->value('is_enable');
		$isEnable2  = PlayGroup::where('code','QiSeBo')->value('is_enable');
		$rateData1  = $rateData2 = [];

		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}
		$listItem1  = [];
		$i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i]['key']  = $key;
			$listItem1[$i]['name'] = $var;
			$balls = explode(',',$wxNumsArr[$key][0]);
			$listItem1[$i]['ball'] = $balls;
			$listItem1[$i]['rate'] = $rateData1[$key]??'--';
			$i += 1;
		}


		$res['listItem1'] = $listItem1;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = $rateData2;
		$res['playArr1']  = $playArr1;
		$res['playArr2']  = $playArr2;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//正肖总肖玩法排列及赔率数据
	public function getZeXiaoRate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','ZeXiao')->where('pan_id',$panId)->first();
		$getRate2   = GameRate::where('game_id',$gameId)->where('group_code','ZongXiao')->where('pan_id',$panId)->first();
		$isEnable1  = PlayGroup::where('code','ZeXiao')->value('is_enable');
		$isEnable2  = PlayGroup::where('code','ZongXiao')->value('is_enable');
		$SX_Nums    = config('enums.SX_NUMS');
		$playArr1   = config('enums.PLAY_SeXiao');
		$playArr2   = config('enums.PLAY_ZongXiao');

		$rateData1 = $rateData2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		if($getRate2&&$isEnable2==1){
			$rateData2 = json_decode($getRate2->rate_data,true);
		}

		$listItem1  = $listItem2 = [];
		$k = $i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i][$k]['key']  = $key;
			$listItem1[$i][$k]['name'] = $var;
			$listItem1[$i][$k]['ball'] = $SX_Nums[$key];
			$listItem1[$i][$k]['rate'] = $rateData1[$key]??'--';
			if(($k+1)%2==0){
				$i += 1;
				$k = 0;
			}else{
				$k += 1;
			}
		}
		$k = 0;
		$i = 0;
		foreach($playArr2 as $key =>$var){
			if($k%2==0){
				$listItem2[0][$i]['key']  = $key;
				$listItem2[0][$i]['name'] = $var;
				$listItem2[0][$i]['rate'] = $rateData2[$key]??'--';
			}else{
				$listItem2[1][$i]['key']  = $key;
				$listItem2[1][$i]['name'] = $var;
				$listItem2[1][$i]['rate'] = $rateData2[$key]??'--';
				$i += 1;
			}
			$k += 1;
		}

		$res['listItem1'] = $listItem1;
		$res['listItem2'] = $listItem2;
		$res['rateData1'] = $rateData1;
		$res['rateData2'] = $rateData2;
		$res['playArr1']  = $playArr1;
		$res['playArr2']  = $playArr2;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//正码号码排列及赔率数据
	public function getZeMa16Rate(Request $request){
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$playArr1   = config('enums.PLAY_ZeMa16');;
		$isEnable1  = PlayGroup::where('code','ZeMa16')->value('is_enable');
		$zemaId     = intval($request->input('zemaId',0));
		$listItem1  = $rateDataAll = $res = [];
		$k = 0;
		if($zemaId>0){
			$rateDataTem  = [];
			$getRate1 = GameRate::where(['game_id'=>$gameId,'group_code'=>'ZeMa16','pan_id'=>$panId,'zema_id'=>$zemaId])->first();
			if($getRate1&&$isEnable1==1){
				$rateDataTem  = json_decode($getRate1->rate_data,true);
			}
			$res['rateData1'] = $rateDataTem;
			$j = 0;
			foreach($playArr1 as $key =>$var){
				$listItem1[$j]['key']  = $key;
				$listItem1[$j]['ball'] = $var;
				$listItem1[$j]['rate'] = $rateDataTem[$key]??'--';
				$j += 1;
			}
			$res['listItem1'] = $listItem1;
			$res['playArr1']  = $playArr1;
		}else{
			for($zmid=1;$zmid<=6;$zmid++){
				$rateDataTem  = [];
				$getRate1 = GameRate::where(['game_id'=>$gameId,'group_code'=>'ZeMa16','pan_id'=>$panId,'zema_id'=>$zmid])->first();
				if($getRate1&&$isEnable1==1){
					$rateDataTem  = json_decode($getRate1->rate_data,true);
				}
				$j = 0;
				foreach($playArr1 as $key =>$var){
					$listItem1[$zmid][$j]['key']  = $key;
					$listItem1[$zmid][$j]['ball'] = $var;
					$listItem1[$zmid][$j]['rate'] = $rateDataTem[$key]??'--';
					$j += 1;
				}
				$rateDataAll[$zmid] = $rateDataTem;
				$k += 1;
			}
			
			$res['listItem1'] = $listItem1;
			$res['rateData1'] = $rateDataAll;
			$res['playArr1']  = $playArr1;
		}
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//连肖玩法排列及赔率数据
	public function getLianXiaoRate(Request $request){
		$clums      = intval($request->input('clums',2));
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$zemaId     = $request->input('zemaId','x2');
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','LianXiao')->where('pan_id',$panId)->where('zema_id',$zemaId)->first();
		$isEnable1  = PlayGroup::where('code','LianXiao')->value('is_enable');
		$playArr1   = config('enums.PLAY_LianXiao');
		$playArr2   = config('enums.PLAY_SeXiao');
		$SX_Nums    = config('enums.SX_NUMS');
		$rateData1  = $listItem1 = $listItem2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}

		$i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i]['key']  = $key;
			$listItem1[$i]['name'] = $var;
			$i += 1;
		}
		$k = $i = 0;
		if($clums==99){
			foreach($playArr2 as $key =>$var){
				$listItem2[$k]['key']  = $key;
				$listItem2[$k]['name'] = $var;
				$listItem2[$k]['rate'] = $rateData1[$key]??'--';
				$listItem2[$k]['ball'] = $SX_Nums[getSXNumIndex($var)]??[];
				$k +=1;
			}
		}else{
			foreach($playArr2 as $key =>$var){
				$listItem2[$i][$k]['key']  = $key;
				$listItem2[$i][$k]['name'] = $var;
				$listItem2[$i][$k]['rate'] = $rateData1[$key]??'--';
				$listItem2[$i][$k]['ball'] = $SX_Nums[getSXNumIndex($var)]??[];
				$k +=1;
				if($k==2){
					$k = 0;
					$i +=1;
				}
			}
		}

		$res['rateData1']  = $rateData1;
		$res['listItem1']  = $listItem1;
		$res['listItem2']  = $listItem2;
		$res['playArr1']   = $playArr1;
		$res['playArr2']   = $playArr2;
		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}

	//连尾玩法排列及赔率数据
	public function getLianWeiRate(Request $request){
		$clums      = intval($request->input('clums',2));
		$gameId     = intval($request->input('gameId',2));
		$panId      = intval($request->input('panId',1));
		$zemaId     = $request->input('zemaId','w2');
		$getRate1   = GameRate::where('game_id',$gameId)->where('group_code','LianWei')->where('pan_id',$panId)->where('zema_id',$zemaId)->first();
		$isEnable1  = PlayGroup::where('code','LianWei')->value('is_enable');
		$playArr1   = config('enums.PLAY_LianWei');
		$playArr2   = $this->PlayArr["LianWei"];
		$rateData1  = $listItem1 = $listItem2 = [];
		if($getRate1&&$isEnable1==1){
			$rateData1 = json_decode($getRate1->rate_data,true);
		}
		$i = 0;
		foreach($playArr1 as $key =>$var){
			$listItem1[$i]['key']  = $key;
			$listItem1[$i]['name'] = $var;
			$listItem1[$i]['rate'] = $rateData1[$key]??'--';
			$i += 1;
		}
		if($clums==99){
			for($j=0;$j<=9;$j++){
				$listItem2[$j]['key']  = $j;
				$listItem2[$j]['name'] = $j.'尾';
				for($b=0;$b<=4;$b++){
					$ball = $j+$b*10;
					if($ball>0) $listItem2[$j]['ball'][$b] = $ball;
				}
				$listItem2[$j]['rate'] = $rateData1[$j]??'--';
			}
		}else{
			for($j=0;$j<=4;$j++){
				$listItem2[$j][0]['key']  = $j;
				$listItem2[$j][0]['name'] = $j.'尾';
				for($b=0;$b<=4;$b++){
					$ball = $j+$b*10;
					if($ball>0) $listItem2[$j][0]['ball'][$b] = $ball;
				}
				$listItem2[$j][0]['rate'] = $rateData1[$j]??'--';
				
				$listItem2[$j][1]['key']  = ($j+5);
				$listItem2[$j][1]['name'] = ($j+5).'尾';
				for($b=0;$b<=4;$b++){
					$ball = $b*10+$j+5;
					$listItem2[$j][1]['ball'][$b] = $ball;
				}
				$listItem2[$j][1]['rate'] = $rateData1[($j+5)]??'--';
			}
		}
		
		$res['rateData1']  = $rateData1;
		$res['listItem1']  = $listItem1;
		$res['listItem2']  = $listItem2;
		$res['playArr1']   = $playArr1;
		$res['playArr2']   = $playArr2;

		return response()->json(['status'=>SUCCESS,'msg'=>'success','content'=>$res]);
	}
	
	//提交投注
	public function postBetData(Request $request){
		$betData  = $request->input('betData');
		$gameId   = intval($request->input('gameId',2));
		$panId    = intval($request->input('panId',1));
		$isMobile = intval($request->input('isMobile',0));
		$agreeChange  = $request->input('agreeChange','true');
		$agreeChange  = $agreeChange=='true'?1:0;
		$actionNumber = $request->input('actionNumber','');
		$faildCount   = 0;
		foreach($betData as $key =>$item){
			if($item['isCheckOk']=='true'&&$item['actionMoney']!=''){
				$data['member_id']     = $this->member->member_id;
				$data['login_name']    = $this->member->login_name;
				$data['nick_name']     = $this->member->nick_name;
				$data['bill_no']       = getBillNo($gameId);
				$data['game_id']       = $gameId;
				$data['zema_id']       = $item['zemaId']??0;
				$data['group_code']    = $item['groupCode'];
				$data['group_name']    = $item['groupName'];
				$data['action_number'] = $actionNumber;
				$data['action_amount'] = $item['actionMoney'];
				$data['action_data']   = $item['actionData'];
				$data['action_key']    = $item['actionKey'];
				$data['action_info']   = $item['actionInfo']??'';
				$data['action_rate']   = $item['actionRate']??0;
				$data['action_pid']    = $panId;
				$data['action_ip']     = getip();
				$data['is_mobile']     = $isMobile;
				$data['agree_change']  = $agreeChange;
				$data['queue_status']  = 'queue_ready';
				$data['created_at']    = date("Y-m-d H:i:s");
				$data['updated_at']    = date("Y-m-d H:i:s");
				try{
					$queueId = QueueBet::insertGetId($data);
					if($queueId){
						HandelBet::dispatch($queueId);                  //加入队列
						log::info('【投注加入队列】queueId=》'.$queueId);
					}else{
						$faildCount =+ 1;
					}
				}catch(Exception $exc){
					Log::error('【添加投注队列信息】失败=>'.$exc->getMessage());
					Log::info($data);
				}
			}
		}
		return response()->json(['status'=>SUCCESS,'msg'=>'投注信息提交成功，请点击投注明细查看投注详单','content'=>'']);
	}

}
