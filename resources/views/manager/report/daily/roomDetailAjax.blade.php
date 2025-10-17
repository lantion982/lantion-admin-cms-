<div class="row">
    <div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0;">
        <ol class="breadcrumb mb1">
            <li>
                <a href="{{route('dataDailyRoom')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">游戏厅日报表</a>
            </li>
            @if ($company_id!='')
                <li>
                    <a href="{{route('dataDailyRoom')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}">
                    {{$company_name}}
                </a>
                </li>
            @endif
            @if ($room_code!='' && array_key_exists($room_code,config('enums.room_code')))
                <li>
                    <a href="{{route('dataDailyRoom')}}?company_id={{$company_id}}&startDate={{$startDate}}&endDate={{$endDate}}&deposit_status={{$room_code}}">
                        {{config('enums.room_code')[$room_code]}}
                    </a>
                </li>
            @endif
            <li>日报表明细</li>
        </ol>
    </div>
</div>
<div class="panel panel-default">
    <div class="box-body table-responsive no-padding">
		<div class="row listtotal">
			<div class="col-xs-12 listtotaltxt">
                投注次数：{{$result['bet_count']}}&nbsp;&nbsp;
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
                <th>游戏厅</th>
                <th class="hidden-xs">投注次数</th>
                <th class="hidden-xs t-c">投注金额</th>
                <th class="t-c">有效投注</th>
                <th class="t-c">输赢</th>
                <th class="hidden-sm hidden-xs t-c">日期</th>
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
						<a class="text-blue" href="{{route('dataDailyRoomUser')}}?company_id={{$company_id}}&deposit_status={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}}&endDate={{$commonReport->calc_date}}">
							{{config('enums.room_code')[$commonReport->room_code]??'-'}}
						</a>
					</td>
                    <td class="hidden-xs">{{$commonReport->bet_count}}</td>
                    <td class="hidden-xs t-r">{{mynumber($commonReport->money_total)}}</td>
                    <td class="t-r">{{mynumber($commonReport->money_use)}}</td>
                    <td class="@if($commonReport->win_lose<0) text-red @endif t-r">
						{{mynumber($commonReport->win_lose)}}
					</td>
                    <td class="hidden-sm hidden-xs t-c" title="更新:{{$commonReport->updated_at}}">
						{{$commonReport->calc_date}}
					</td>
                    <td class="t-c">
                        @if(\App\Libs\Helper::isSuper()&&$commonReport->company_id==='company_super')
                            @if($commonReport->is_locked)
                                <a href="javascript:" class="text-green" id="lockUnLock{{$commonReport->data_daily_room_id}}"
                                     onclick="{if(confirm('确认解除该报表锁定状态吗？')){dailyLockUnLock('{{$commonReport->data_daily_room_id}}');return true;}return false;}">已锁</a>
                            @else
								<a href="javascript:" title="重新统计" class="text-blue"
									onclick="{if(confirm('确认重新统计该日报表吗？')){redoSum('{{$commonReport->data_daily_room_id}}');return true;}return false;}">统计</a>
								&nbsp;|&nbsp;
								<a  href="javascript:" class="text-red" id="lockUnLock{{$commonReport->data_daily_room_id}}"
                                     onclick="{if(confirm('确认锁定该报表吗？')){dailyLockUnLock('{{$commonReport->data_daily_room_id}}');return true;}return false;}">未锁</a>
                            @endif
							<span class="hidden-xs">
                            &nbsp;|&nbsp;
                            <a href="{{route('dataDailyRoomUser')}}?company_id={{$company_id}}&deposit_status={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}}&endDate={{$commonReport->calc_date}}"
                               title="按游戏厅查看明细">游戏厅</a>
                            &nbsp;|&nbsp;
                            <a href="{{route('dataDailyRoomUserGtype')}}?company_id={{$company_id}}&deposit_status={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}}&endDate={{$commonReport->calc_date}}"
                               title="按游戏类型查看明细">类型</a>
							</span>
                        @else
							<span class="hidden-xs">
                            <a href="{{route('dataDailyRoomUser')}}?company_id={{$company_id}}&deposit_status={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}}&endDate={{$commonReport->calc_date}}"
								title="按游戏厅查看明细">游戏厅</a>
                            &nbsp;|&nbsp;
							</span>
                            <a href="{{route('dataDailyRoomUserGtype')}}?company_id={{$company_id}}&deposit_status={{$commonReport->room_code}}&startDate={{$commonReport->calc_date}}&endDate={{$commonReport->calc_date}}"
								title="按游戏类型查看明细">按类型</a>

                        @endif
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
