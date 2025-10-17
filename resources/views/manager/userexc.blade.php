<table id="tbl-activities" class="table table-hover">
	<thead>
	<tr>
		<th>帐号</th>
		<th>等级</th>
		<th>余额</th>
		<th>积分</th>
		<th>姓名</th>
		<th>开户城市</th>
		<th>手机号</th>
		<th>QQ号</th>
		<th>微信</th>
		<th>注册时间</th>
		<th>注册IP</th>
		<th>最后登录</th>s
		<th>最后存款</th>
		<th>存款金额</th>
		<th>登陆状态</th>
		<th>异常状态</th>
	</tr>
	</thead>
	,a.,a.,a.,a.,a.qq,a.,a.,a.,a.,c.created_at,c.deposit_money,
	a.is_allow_sign,a.security_level_code FROM `tb_member` a LEFT join tb_member_level b on a.member_level_id = b.member_level_id
	<tbody>
	@foreach($list as $key=>$var)
		<tr>
			<td>{{$var->display_name}}</td>
			<td>{{$var->memberLevel->member_level_name}}</td>
			<td>{{$var->balance}}</td>
			<td>{{$var->points}}</td>
			<td>{{$var->real_name}}</td>
			<td>{{$var->register_area}}</td>
			<td>{{$var->phone}}</td>
			<td>{{$var->qq}}</td>
			<td>{{$var->wechat}}</td>
			<td>{{$var->register_time}}</td>
			<td>{{$var->register_ip}}</td>
			<td>{{$var->late_login_time}}</td>
			<td>{{$var->lastdes}}</td>
			<td>{{$var->lastmoney}}</td>
			<td>@if ($var->is_allow_sign==1) 正常 @else 禁止 @endif </td>
			<td>@if ($var->security_level_code=='security_level_0') 异常 @else 正常 @endif</td>
		</tr>
	@endforeach
	</tbody>
</table>
<div class="box-info">
	<div class="col-lg-12">
		{{$list->links()}}
	</div>
</div>