<div class="row listtotal">
	<div class="col-xs-12 listtotaltxt">
		合计：{{$list->total()}} 笔&nbsp;{{mynumber($results['money_change'])}} 元
	</div>
</div>
<table id="tbl-activities" class="table table-hover">
	<input type="hidden" id="page" value="{{$page}}">
	<thead>
	<tr>
		<th class="t-c">序</th>
		<th>单号</th>
        <th>账号</th>
		<th class="hidden-sm hidden-xs">变动前</th>
		<th>变动金额</th>
		<th class="hidden-sm hidden-xs">变动后</th>
		<th class="hidden-xs">类型</th>
		<th>日期时间</th>
		<th class="hidden-sm hidden-xs">操作人</th>
		<th class="hidden-sm hidden-xs">备注</th>
	</tr>
	</thead>
	<tbody>
	@foreach($list as $key=>$val)
		<tr>
			<td class="t-c">{{++$key+($page_count*($page-1))}}</td>
            <td>{{$val->bill_no}}</td>
			<td>{{$val->member->login_name??'-'}}</td>
			<td class="@if ($val->money_before<0) text-red @endif hidden-sm hidden-xs">
				{{$val->money_before}}
			</td>
			<td class="@if ($val->money_change<0) text-red @endif">{{$val->money_change}}</td>
			<td class="@if ($val->money_after<0) text-red @endif hidden-sm hidden-xs">
				{{$val->money_after}}
			</td>
			<td class="hidden-xs">{{config('enums.move_type')[$val->move_type]}}</td>
			<td title="{{$val->created_at}}">
				{{$val->created_at}}
			</td>
			<td class="hidden-sm hidden-xs">{{$val->admin->login_name??'系统'}}</td>
			<td class="hidden-sm hidden-xs">{{str_limit($val->remarks,100)}}</td>
		</tr>
	@endforeach
	</tbody>
</table>
<div class="box-info">
	{{$list->links()}}
</div>
