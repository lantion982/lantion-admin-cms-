<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PERMISSION_ID" value="{{$parentPermission->id}}">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <thead>
    <tr>
        <th class="hidden-sm hidden-xs"></th>
        <th class="hidden-sm hidden-xs t-c">排序</th>
		<th>路由</th>
        <th>名称</th>
        <th class="hidden-sm hidden-xs">图标</th>
        <th>类型</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($permissions as $key=>$permission)
        <tr>
            <td class="hidden-sm hidden-xs">
                <label for="id-{{$permission->id}}"></label>
                @if(count($permission->sub_permission)>0)
					<a class="show-sub-permissions" data-id="{{$permission['id']}}">
						<span class="glyphicon glyphicon-chevron-right"></span>
					</a>
                @endif
            </td>
            <td class="hidden-sm hidden-xs t-c">{{$permission->sorts}}</td>
			<td>{{$permission->name}}</td>
            <td>{{$permission->title}}</td>
            <td class="hidden-sm hidden-xs">{{$permission->icon}}</td>
            <td>
                {!!$permission->ptype=='menu'?'<span class="label label-danger">':'<span class="label label-success">'!!}
                {{$permission->ptype}}
                {!!'</span>'!!}
            </td>
            <td class="t-c">
                <a class="btn btn-info btn-sm" onclick="permpage_Info('{{$permission->id}}')">
					<i class="fa fa-pencil"></i>&nbsp;详情
				</a>
                <a href="javascript:" class="btn btn-danger btn-sm" onclick="delPagePermission('{{$permission->id}}')">
					<i class=" fa fa-trash-o"></i>&nbsp;删除
				</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
       {{$permissions->links()}}
    </div>
</div>
