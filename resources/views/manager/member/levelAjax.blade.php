<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>编号</th>
        <th>名称</th>
        <th class="hidden-xs">会员数</th>
        <th class="hidden-xs t-c" title="赠送积分">赠送积分</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($levels as $key=>$level)
        <tr>
            <td class="t-c">{{++$key+($page_count*($page-1))}}</td>
            <td>{{$level->level_code}} </td>
            <td>{{$level->level_name}}</td>
            <td class="hidden-xs">{{$level->members()->count()??0}}</td>
            <td class="hidden-xs t-r">{{mynumber($level->gift_money)}}</td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="levelInfo('{{$level->id}}')">详情</a>
				&nbsp;|&nbsp;
                <a href="javascript:" class="text-red" onclick="delLevel('{{$level->id}}')">删除</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
	{{$levels->links()}}
</div>
