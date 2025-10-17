<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
        <tr>
            <th></th>
            <th>显示名称</th>
            <th>路由</th>
            <th>图标</th>
            <th>类型</th>
            <th style="text-align:center;">操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($permissions as $key=>$permission)
            <tr>
                <td>
                    <label for="id-{{$permission->id}}"></label>
                    @if(count($permission->sub_permission)>0)
                        <a class="show-sub-permissions" data-id="{{$permission['id']}}">
                            <span class="glyphicon glyphicon-chevron-right"></span>
                        </a>
                    @endif
                </td>
                <td>
                    <p class="text-info">{{$permission->title}}</p>
                </td>
                <td>{{$permission->name}}</td>
                <td>{{$permission->icon}}</td>
                <td>
                    @if($permission->ptype == 'menu')
                        <span class="label label-danger">{{$permission->ptype}}</span>
                    @else
                        <span class="label label-success">{{$permission->ptype}}</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <a class="btn btn-info btn-sm" onclick="permissionInfo('{{$permission->id}}')">
                        <i class="fa fa-pencil"></i>&nbsp;编辑
                    </a>
                    <a class="btn btn-danger btn-sm" onclick="delPermission('{{$permission->id}}')">
                        <i class=" fa fa-trash-o"></i>&nbsp;删除
                    </a>
                </td>
            </tr>
            <!--二级-->
            @if(count($permission->sub_permission)>0)
                @foreach($permission->sub_permission as $sub)
                    @if($sub->permission_type <> 'func')
                        <tr class=" parent-permission-{{$permission->id}} hide">
                            <td>
                                <label></label>
                                <label for="id-{{$sub->id}}"></label>
                            </td>
                            <td>
                                |-- {{$sub->title}}
                                @if(count($sub->sub_permission)>0)
                                    <a class="show-sub-permissions" data-id="{{$sub['id']}}">
                                        <span class="glyphicon glyphicon-chevron-right"></span>
                                    </a>
                                @endif
                            </td>
                            <td>{{$sub->name}}</td>
                            <td>{{$sub->icon}}</td>
                            <td>
                                @if($sub->ptype == 'menu')
                                    <span class="label label-danger">{{$sub->ptype}}</span>
                                @else
                                    <span class="label label-success">{{$sub->ptype}}</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <a class="btn btn-info btn-sm" onclick="permissionInfo('{{$sub->id}}')">
                                    <i class="fa fa-pencil"></i>&nbsp;编辑
                                </a>
                                @if($sub->ptype == 'menu')
                                    <a class="btn btn-success btn-sm" onclick="createSubPermission('{{$sub->id}}')"
                                        data-original-title="新增下级权限">
                                        <i class="glyphicon glyphicon-plus"></i>&nbsp;新增权限
                                    </a>
                                @endif
                                @if($sub->ptype == 'page')
                                    <a class="btn btn-success btn-sm" onclick="permPageFunc('{{$sub->id}}')">
                                        <i class="fa fa-wrench"></i>&nbsp;页面权限</a>
                                @endif
                                <a class="btn btn-danger btn-sm" onclick="delPermission('{{$sub->id}}')">
                                    <i class=" fa fa-trash-o"></i>&nbsp;删除
                                </a>
                            </td>
                        </tr>
                        <!--三级-->
                        @if(count($sub->sub_permission)>0)
                            @foreach($sub->sub_permission as $lv3)
                                <tr class=" parent-permission-{{$sub->id}} hide">
                                    <td>
                                        <label></label>
                                        <label for="id-{{$lv3->id}}"></label>
                                    </td>
                                    <td>
                                        &nbsp;&nbsp;&nbsp;|---{{$lv3->title}}
                                    </td>
                                    <td>{{$lv3->name}}</td>
                                    <td>{{$lv3->icon}}</td>
                                    <td>
                                        @if($lv3->ptype == 'menu')
                                            <span class="label label-danger">{{$lv3->ptype}}</span>
                                        @elseif($lv3->ptype == 'func')
                                            <span class="label label-warning">{{$lv3->ptype}}</span>
                                        @else
                                            <span class="label label-success">{{$lv3->ptype}}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <a class="btn btn-info btn-sm" onclick="permissionInfo('{{$lv3->id}}')">
                                            <i class="fa fa-pencil"></i>&nbsp;编辑
                                        </a>
                                        <a class="btn btn-success btn-sm @if($lv3->ptype != 'page') disabled @endif" onclick="permPageFunc('{{$lv3->id}}')">
                                            <i class="fa fa-wrench"></i>&nbsp;页面权限
                                        </a>
                                        <a class="btn btn-danger btn-sm" onclick="delPermission('{{$lv3->id}}')">
                                            <i class=" fa fa-trash-o"></i>&nbsp;删除
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        @endif
                    @endif
                @endforeach
            @endif

        @endforeach
    </tbody>
</table>

<div class="box-info">
    <div class="col-lg-12">
        {{$permissions->links()}}
    </div>
</div>
