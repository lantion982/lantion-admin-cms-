<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>名称</th>
        <th class="hidden-sm hidden-xs">描述</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($roles as $key=>$role)
        <tr>
            <td class="t-c">{{++$key+($page_count*($page-1))}}</td>
            <td>{{$role->title}}</td>
            <td class="hidden-sm hidden-xs">{{$role->remarks}}</td>
			<td class="t-c">
				<a href="javascript:" class="text-blue" onclick="roleInfo('{{$role->id}}')">详情</a>
				&nbsp;|&nbsp;
				<a href="javascript:" class="text-green" onclick="rolePermission('{{$role->id}}')">权限</a>
				&nbsp;|&nbsp;
				<a href="javascript:" class="text-red" onclick="delRole('{{$role->id}}')">删除</a>
			</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
	{{$roles->links()}}
</div>
