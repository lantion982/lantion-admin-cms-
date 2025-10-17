@if($list)
    <div class="row listtotal">
        <div class="col-xs-12 listtotaltxt">
            投注注单：{{$list->total()}}&nbsp;&nbsp;
            投注额：{{mynumber($results['bet_amount'])}}&nbsp;&nbsp;
            有效投注：{{mynumber($results['valid_bet_amount'])}}&nbsp;&nbsp;
            输赢：<span class="@if($results['net_amount']<0) text-red @endif">
                {{mynumber($results['net_amount'])}}
            </span>
        </div>
    </div>
@endif
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th class="hidden-md hidden-xs">单号</th>
        <th>账号</th>
		{{--<th class="hidden-md hidden-xs">代理</th>--}}
        <th class="hidden-md hidden-xs">游戏厅</th>
        <th class="hidden-md hidden-xs">游戏类型</th>
        <th class="hidden-md hidden-xs">游戏名称</th>
		<th>投注明细</th>
        <th>投注额</th>
        <th>有效投注</th>
        <th>输赢</th>
        <th>结算</th>
        <th class="t-c hidden-md hidden-xs">投注时间</th>
        <th class="t-c hidden-md hidden-xs">结算时间</th>
    </tr>
    </thead>
    <tbody>
    @if($list)
    @foreach($list as $key=>$var)
        <tr>
            <td class="t-c">
                {{++$key+($page_count*($current_page-1))}}
            </td>
            <td class="hidden-md hidden-xs">{{$var->bill_no}}</td>
            <td>
				@if(\App\Libs\Helper::isSuper())
					{{$var->player_name}}
				@else
					{{substr($var->player_name,2)}}
				@endif
			</td>
			{{--<td>{{$var->agent_name}}</td>--}}
            <td class="hidden-md hidden-xs">{{$rooms[$var->room_code]??'-'}}</td>
            <td class="hidden-md hidden-xs">{{config('enums.game_type_code')[$var->game_type]??''}}</td>
            <td class="hidden-md hidden-xs">{{$var->game_name}}</td>
			<th>{{$var->remark}}</th>
            <td>{{mynumber($var->bet_amount)}}</td>
            <td>{{mynumber($var->valid_bet_amount)}}</td>
            <td class="@if($var->net_amount<0) text-red @endif">
                {{mynumber($var->net_amount)}}
            </td>
            <td>{{$var->is_settle?'是':'否'}}</td>
            <td class="t-c hidden-md hidden-xs">{{$var->bet_time}}</td>
            <td class="t-c hidden-md hidden-xs">{{$var->settle_time}}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
	{{$list->links()}}
</div>
@endif