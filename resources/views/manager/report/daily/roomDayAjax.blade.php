<div class="row">
    <div class="col-lg-6 col-sm-6" style="font-size:15px;line-height:30px;padding:0 10px 0 0!important;">
        <ol class="breadcrumb mb1">
            <li>
                <a href="{{route('dataDailyRoom')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
					游戏厅日报表
				</a>
            </li>
            @if ($room_code!=''&&array_key_exists($room_code,config('enums.room_code')))
                <li>
                    {{config('enums.room_code')[$room_code]}}
                </li>
            @else
            <li class="active">所有游戏厅</li>
            @endif
        </ol>
    </div>
    <div class="col-lg-6 col-sm-6" style="font-size:15px;line-height:30px;padding:0!important;">
        <ol class="breadcrumb mb1 text-lg text-md-center">
            <li>【<strong>{{$startDate}} ~ {{$endDate}}</strong>】</li>
        </ol>
    </div>
</div>
<div class="panel panel-default">
    <div class="box-body table-responsive no-padding">
		<div class="row listtotal">
			<div class="col-xs-12 listtotaltxt">
				投注次数：{{$dataSum['bet_count'] }}&nbsp;&nbsp;
				投注额：{{mynumber($dataSum['money_total'])}}&nbsp;&nbsp;
				有效投注：{{mynumber($dataSum['money_use'])}}&nbsp;&nbsp;
				输赢：<span class="@if($dataSum['win_lose']<0) text-red @endif">{{mynumber($dataSum['win_lose'])}}</span>
			</div>
		</div>
        <table id="tbl-activities" class="table table-hover">
			<input type="hidden" id="page">
            <thead>
			<tr>
				<th class="t-c">序</th>
				@if(\App\Libs\Helper::isSuper())
					<th class="hidden-sm hidden-xs">公司</th>
				@endif
				<th>游戏厅</th>
				<th class="hidden-xs">投注次数</th>
				<th class="hidden-xs t-c">投注金额</th>
				<th class="t-c">有效投注</th>
				<th class="t-c">输赢</th>
				<th class="t-c">操作</th>
			</tr>
            </thead>
            <tbody>
            @foreach($list as $key=>$val)
                <tr>
                    <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
					@if(\App\Libs\Helper::isSuper())
						<td class="hidden-sm hidden-xs">
							{{$val['company_id']!=='company_super'?$val['company_name']:'平台'}}
						</td>
					@endif
                    <td>{{$val['room_name']}}</td>
                    <td class="hidden-xs">{{$val['bet_count']}}</td>
                    <td class="hidden-xs t-r">{{mynumber($val['money_total'])}}</td>
                    <td class="t-r">{{mynumber($val['money_use'])}}</td>
                    <td class="@if($val['win_lose']<0) text-red @endif t-r">
                        {{mynumber($val['win_lose'])}}
                    </td>
                    <td class="t-c">
                        <a title="查看明细" class="text-blue" href="{{route('dataDailyRoom_detail')}}?company_id={{$val['company_id']}}&deposit_status={{$val['room_code']}}&startDate={{$val['startDate']}}&endDate={{$val['endDate']}}">
                            明细
						</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tr>
                <td>&nbsp;</td>
                @if(\App\Libs\Helper::isSuper())
                    <td class="hidden-sm hidden-xs">&nbsp;</td>
                @endif
                <td>合计：</td>
                <td class="hidden-xs">{{$dataSum['bet_count']}}</td>
                <td class="hidden-xs t-r">{{mynumber($dataSum['money_total'])}}</td>
                <td class="t-r">{{mynumber($dataSum['money_use'])}}</td>
                <td class="t-r">
                    <span class="@if($dataSum['win_lose']<0) text-red @endif">{{mynumber($dataSum['win_lose'])}}</span>
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>
</div>

