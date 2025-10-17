<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>账号</th>
        <th>昵称</th>
        <th>等级</th>
        <th>余额</th>
        <th class="hidden-sm hidden-xs">相册目录</th>
		<th class="hidden-sm hidden-xs">注册时间</th>
        <th class="hidden-md hidden-sm hidden-xs">最后登录</th>
        <th class="hidden-xs">状态</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($members as $key=>$member)
        <tr class="@if (str_is($member->allow_login,'0')) text-gray @endif">
            <td clas="t-c">{{++$key+($page_count*($page-1))}}</td>
            <td>{{$member->login_name}}</td>
            <td>{{$member->nick_name}}</td>
            <td>{{$member->memberLevel->level_name??'-'}}</td>
            <td class="@if($member->balance<0) text-red @endif ">{{$member->balance}}</td>
			<td class="hidden-sm hidden-xs">{{$member->picpath}}</td>
            <td class="hidden-sm hidden-xs">{{$member->created_at}}</td>
            <td class="hidden-md hidden-sm hidden-xs">{{$member->late_login_time}}</td>
            <td class="@if(str_is($member->is_allow,'0')) text-red @endif hidden-xs">
				{{str_is($member->is_allow,'1')?'正常':'冻结'}}
			</td>
            <td class="t-c">
                <a href="javascript:" onclick="memberAccountInfo('{{$member->id}}')" class="text-blue">详情</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
	{{$members->links()}}
</div>
