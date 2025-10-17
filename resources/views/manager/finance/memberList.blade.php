{{--<div class="row listtotal">
	<div class="col-xs-12 listtotaltxt">
		总计：{{mynumber($results['balance'])}}&nbsp;&nbsp;
		本页小计：{{sprintf('%.2f',$members->sum('balance'))}}
	</div>
</div>--}}
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
	<thead>
	<tr>
		<th class="t-c">序</th>
		<th>账号</th>
		<th class="hidden-xs">昵称</th>
		<th class="hidden-sm hidden-xs">等级</th>
		<th>余额</th>
		<th class="hidden-sm hidden-xs">注册时间</th>
		<th class="t-c">操作</th>
	</tr>
	</thead>
	<tbody>
	@foreach($members as $key=>$member)
		<tr class="@if ($member->is_allow==0) text-gray @endif">
			<td class="t-c">{{++$key+($page_count*($page-1))}}</td>
			<td>{{$member->login_name}}</td>
			<td class="hidden-xs">{{$member->nick_name??'-'}}</td>
			<td class="hidden-sm hidden-xs">{{$member->memberLevel->member_level_name??'-'}}</td>
			<td class="@if($member->balance<0) text-red @endif ">{{$member->balance}}</td>
			<td class="hidden-sm hidden-xs">{{$member->register_time}}</td>
			<td class="t-c">
				<a href="javascript:" class="text-green" onclick="balanceInfo('{{$member->id}}')">额度</a>
			</td>
		</tr>
	@endforeach
	</tbody>
</table>
<div class="box-info">
	{{$members->links()}}
</div>
