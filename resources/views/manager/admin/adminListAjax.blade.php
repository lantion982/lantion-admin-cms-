<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>帐号</th>
        <th>角色</th>
        <th class="hidden-sm">允许登录</th>
		<th class="hidden-sm hidden-xs">添加日期</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($admins as $key=>$val)
        <tr>
            <td class="t-c">
				{{++$key+($page_count*($page-1))}}
            </td>
            <td>{{$val->login_name}}</td>
            <td>
                @if($val->roles()->count())
                    @foreach($val->roles()->get() as $role)
                        <span class="label label-success">{{$role->title}}</span>
                    @endforeach
                @else
                    <span class="badge">无</span>
                @endif
            </td>
            <td class="hidden-sm">
				<input type="checkbox" value="{{$val->id}}" {{str_is($val->is_allow,'1')?'checked':''}}
					class="switch">
			</td>
			<td class="hidden-sm hidden-xs">{{$val->created_at}}</td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="adminInfo('{{$val->id}}')">详情</a>
				&nbsp;|&nbsp;
                <a href="javascript:" class="text-red" onclick="delAdmin('{{$val->id}}')">删除</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
	{{$admins->links()}}
</div>
