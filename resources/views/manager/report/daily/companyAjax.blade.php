<div class="row">
    <div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0;">
        <ol class="breadcrumb mb1">
            <li>
                <a href="{{route('dataDailySuper')}}?startDate={{$startDate}}&endDate={{$endDate}}">
                    公司汇总表
                </a>
            </li>
            <li>
                时间：{{$startDate}}--{{$endDate}}
            </li>
        </ol>
    </div>
</div>

<div class="panel panel-default">
    <div class="box-body table-responsive no-padding">
		<div class="row listtotal">
			<div class="col-xs-12 listtotaltxt">
                投注次数：{{$result['bet_count']}}&nbsp;&nbsp;
                投注额：{{mynumber($result['money_total'])  }}&nbsp;&nbsp;
                有效投注：{{mynumber($result['money_use'])}}&nbsp;&nbsp;
                输赢：<span class="@if($result['win_lose']<0) text-red @endif">
                    {{mynumber($result['win_lose'])}}
                </span>

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
                <th class="hidden-xs">投注次数</th>
                <th class="t-c">投注金额</th>
                <th class="t-c">有效投注</th>
                <th class="t-c">输赢</th>
                <th class="t-c hidden-sm hidden-xs">日期</th>
                <th class="t-c">操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($reports as $key=>$report)
                <tr>
                    <td class="t-c">
                        {{++$key+($page_count*($current_page-1))}}
                    </td>
                    @if(\App\Libs\Helper::isSuper())
                        <td class="hidden-sm hidden-xs">
							{{$report->company_id!=='company_super'?$report->company->name:'平台'}}
						</td>
                    @endif
                    <td class="hidden-xs">{{$report->bet_count}}</td>
                    <td class="t-r">{{mynumber($report->money_total)}}</td>
                    <td class="t-r">{{mynumber($report->money_use)}}</td>
                    <td class="@if ($report->win_lose<0) text-red @endif t-r">
                        {{mynumber($report->win_lose)}}
                    </td>
                    <td class="t-c hidden-sm hidden-xs">{{$report->calc_date}}</td>
                    <td class="t-c">
                        <a title="点击查看明细"
							href="{{route('dataDailyRoom_detail')}}?company_id={{$report->company_id}}&startDate={{$report->calc_date}}&endDate={{$report->calc_date}}">
							明细
						</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="box-info">
			{{$reports->links()}}
        </div>
    </div>
</div>
