<div class="row listtotal">
	<div class="col-xs-12 listtotaltxt">
        总计：{{$drawApplies->total()}} 笔&nbsp;{{mynumber($results['draw_money'])}}&nbsp;&nbsp;
		本页小计：{{sprintf('%.2f',$drawApplies->sum('draw_money'))}}
    </div>
</div>
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count }}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page }}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th class="hidden-sm hidden-xs">单号</th>
        <th>账号</th>
        <th>金额</th>
        <th>手续费</th>
        <th class="hidden-sm hidden-xs">首取</th>
        <th class="hidden-sm hidden-xs">出款方式</th>
        <th class="hidden-xs">申请时间</th>
        <th class="hidden-sm hidden-xs">处理时间</th>
        <th>状态</th>
        <th class="hidden-md hidden-sm hidden-xs">操作人</th>
        <th class="hidden-md hidden-sm hidden-xs" style="width:200px;">备注</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($drawApplies as $key=>$val)
        <tr id="tr{{$id=$key}}">
            <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
            <td class="hidden-sm hidden-xs">{{$val->bill_no}}</td>
            <td>{{$val->member_agent->display_name??'-'}}</td>
            <td>{{$val->draw_money}}</td>
            <td class="@if ($val->draw_fee<=0) text-gray @endif" >{{$val->draw_fee}}</td>
            <td class="{{$val->is_first_draw?'text-red':'text-gray'}} hidden-sm hidden-xs">
				{{$val->is_first_draw?'是':'否'}}
			</td>
            <td class="hidden-sm hidden-xs">
                @if($val->draw_status=='apply'||$val->draw_status=='accept')
                    --
                @elseif(empty($val->payment_account_id))
                    手动出款
                @else
                    {{$val->paymentAccount->account_name??'-'}}
                @endif
            </td>
            <td class="hidden-xs">
				@if(isset($val->created_at))
					{{$val->created_at}}
				@endif
			</td>
            <td class="hidden-sm hidden-xs">{{$val->accept_time}}</td>
            <td class="{{config('enums.draw_status_color')[$val->draw_status]}}">
                {{config('enums.draw_status')[$val->draw_status]}}
                @if($val->draw_status =='accept')
					<a href="javascript:" class="text-blue" onclick="postDrawThird('{{$val->draw_apply_id}}')">
						[出款]
					</a>
                @endif
                @if($val->draw_status =='audit')
					<a href="javascript:" class="text-green" onclick="queryMemberDraw('{{$val->draw_apply_id}}')">
						[进度]
					</a>
                @endif
            </td>
            <td class="hidden-md hidden-sm hidden-xs">{{$val->admin->login_name??'系统'}}</td>
            <td class="hidden-md hidden-sm hidden-xs">{{$val->description}}</td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="getDrawInfo('{{$val->draw_apply_id}}')">详情</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
	{!!$drawApplies->links()!!}
</div>
