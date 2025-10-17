<div class="row">
	<div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0;">
		<ol class="breadcrumb mb1">
			<li>
				<a href="{{route('dataDailyGameType')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
					游戏类型-日报表
				</a>
			</li>
			@if ($company_id)
				<li>
					<a href="{{route('dataDailyGameType')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
						{{$company_name}}
					</a>
				</li>
			@endif
			@if ($game_type_code!=''&&array_key_exists($game_type_code,config('enums.game_type_code')))
				<li>
					<a href="{{route('dataDailyGameType')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}&game_type_code={{$game_type_code}}">
						{{config('enums.game_type_code')[$game_type_code]}}
					</a>
				</li>
			@endif
			<li>
				{{$startDate}}-{{$endDate}}
			</li>
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
			<input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
			<thead>
			<tr>
				<th class="t-c">序</th>
				@if(\App\Libs\Helper::isSuper())
					<th class="hidden-sm hidden-xs">公司</th>
				@endif
				<th>类型</th>
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
					<td class="t-c">{{$key+1}}</td>
					@if(\App\Libs\Helper::isSuper())
						<td class="hidden-sm hidden-xs">
							{{$val['company_id']!=='company_super'?$val['company_name']:'平台'}}
						</td>
					@endif
					<td>
						{{$val['game_type_name']}}
					</td>
					<td class="hidden-xs">{{mynumber($val['bet_count'])}}</td>
					<td class="hidden-xs t-r">{{mynumber($val['money_total'])}}</td>
					<td class="t-r">{{mynumber($val['money_use'])}}</td>
					<td class="@if($val['win_lose']<0) text-red @endif t-r">
						{{mynumber($val['win_lose'])}}
					</td>
					<td class="t-c">
						<a title="点击查看明细" class="text-blue"
							href="{{route('dataDailyGameType_detail')}}?company_id={{$val['company_id']}}&game_type_code={{$val['game_type_code']}}&startDate={{$val['startDate']}}&endDate={{$val['endDate']}}">
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
				<td class="hidden-xs  t-r">{{mynumber($dataSum['money_total'])}}</td>
				<td class="t-r">{{mynumber($dataSum['money_use'])}}</td>
				<td class="t-r">
                    <span class="@if($dataSum['win_lose']<0) text-red @endif">
                        {{mynumber($dataSum['win_lose'])}}
                    </span>
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
	</div>
</div>
