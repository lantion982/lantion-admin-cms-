<div class="row">
    <div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0!important;">
        <ol class="breadcrumb mb1">
            <li>
                <a href="{{route('dataDailyRoom_detail')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
                    日报表明细
                </a>
            </li>
            @if ($company_id!='')
                <li>
                    <a href="{{route('dataDailyRoom_detail')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
                        {{$company_name}}
                    </a>
                </li>
            @endif
            @if ($room_code!='' && array_key_exists($room_code,config('enums.room_code')))
                <li>
                    <a href="{{route('dataDailyRoom_detail')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}&deposit_status={{$room_code}}">
                        {{config('enums.room_code')[$room_code]}}
                    </a>
                </li>
            @endif
            <li>{{$startDate}}--{{$endDate}}</li>
        </ol>
    </div>
</div>
<div class="panel panel-default">
    <div id="ajaxContent"  class="box-body table-responsive no-padding">
        <div class="row listtotal">
			<div class="col-xs-12 listtotaltxt">
                投注次数：{{$result['bet_count'] }}&nbsp;&nbsp;
                投注金额：{{mynumber($result['money_total'])}}&nbsp;&nbsp;
                有效投注：{{mynumber($result['money_use'])}}&nbsp;&nbsp;
                输赢：<span class="@if($result['win_lose']<0) text-red @endif">{{mynumber($result['win_lose'])}}</span>
            </div>
        </div>
        <table id="tbl-activities" class="table table-hover">
			<input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
			<input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
            <thead>
            <tr>
                <th class="t-c">序</th>
				@if(\App\Libs\Helper::isSuper())
                	<th class="hidden-sm hidden-xs">公司</th>
				@endif
                <th>账号</th>
                <th class="hidden-xs">游戏厅</th>
                <th class="hidden-sm hidden-xs">投注次数</th>
                <th class="t-c hidden-xs">投注金额</th>
                <th class="t-c">有效投注</th>
                <th class="t-c">输赢</th>
                <th class="t-c hidden-sm hidden-xs">日期</th>
                <th class="t-c">操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($commonReports as $key=>$commonReport)
                <tr>
                    <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
					@if(\App\Libs\Helper::isSuper())
					<td class="hidden-sm hidden-xs">
						{{$commonReport->company_id!=='company_super'?$commonReport->company->name:'平台'}}
					</td>
					@endif
                    <td>
						@if(\App\Libs\Helper::isSuper())
							{{$commonReport->login_name}}
						@else
							{{substr($commonReport->login_name,2)}}
						@endif
					</td>
                    <td class="hidden-xs">{{config('enums.room_code')[$commonReport->room_code]}}</td>
                    <td class="hidden-sm hidden-xs">{{$commonReport->bet_count}}</td>
                    <td class="t-r hidden-xs">{{$commonReport->money_total}}</td>
                    <td class="t-r">{{$commonReport->money_use}}</td>
                    <td class="@if($commonReport->win_lose<0) text-red @endif t-r">
                        {{$commonReport->win_lose}}
                    </td>
                    <td class="t-c hidden-sm hidden-xs">{{$commonReport->calc_date}}</td>
                    <td class="t-c">
                        <a href="javascript:" title="点击查看明细"  class="text-blue"
							onclick="myaddTabs('gamePlaylist{{++$key+($page_count*($current_page-1))}}','投注明细','{{route('gameData')}}?company_id={{$commonReport->company_id}}&keyword={{$commonReport->login_name}}&room_code={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}} 00:00:00&endDate={{$commonReport->calc_date}} 23:59:59');">
                            明细
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="box-info">
            <div class="col-lg-12">
                {{$commonReports->links()}}
            </div>
        </div>
    </div>
</div>
