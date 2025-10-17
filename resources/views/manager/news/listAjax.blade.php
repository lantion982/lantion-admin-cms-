<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th>序</th>
        <th>标题</th>
        <th class="hidden-sm hidden-xs">开始时间</th>
        <th class="hidden-sm hidden-xs">结束时间</th>
        <th class="t-c hidden-xs">排序</th>
        <th class="t-c hidden-xs">状态</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($news as $key=>$new)
        <tr>
            <td>{{++$key+($page_count*($current_page-1))}}</td>
            <td>{{str_limit($new->title,36)}}</td>
            <td class="hidden-sm hidden-xs">{{$new->begin_time}}</td>
            <td class="hidden-sm hidden-xs">{{$new->end_time}}</td>
            <td class="t-c hidden-xs">{{$new->sorts}}</td>
            <td class="t-c hidden-xs">
                @if($new->is_show==0)
                    <bottn class="btn btn-default btn-sm" onclick="newsUpdate('{{$new->id}}',1)">未发布</bottn>
                @elseif($new->is_show==1)
                    <bottn class="btn btn-success btn-sm" onclick="newsUpdate('{{$new->id}}',2)">发布中</bottn>
                @elseif($new->is_show==2)
                    <bottn class="btn btn-danger btn-sm" onclick="newsUpdate('{{$new->id}}',0)">已结束</bottn>
                @endif
            </td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="newsEdit('{{$new->id}}')">详情</a>&nbsp;|
                <a href="javascript:" class="text-red" onclick="newsDel('{{$new->id}}')">删除</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
   {{$news->links()}}
</div>
