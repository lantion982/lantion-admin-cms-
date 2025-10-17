<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>接收用户</th>
        <th>信息类型</th>
        <th>信息内容</th>
        <th>发送人</th>
        <th>IP</th>
        <th>发送时间</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($list as $key=>$var)
        <tr>
            <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
            <td>{{$var->member_agent_name}}</td>
            <td>@if($var->message_pid==0)系统信息@else用户私信@endif</td>
            <td>{{str_limit($var->message_body,50)}}</td>
            <td>{{str_limit($var->from_username,30)}}</td>
            <td>{{$var->message_ip}}</td>
            <td>{{$var->created_at}}</td>
            <td class="t-c">
                <a href="javascript:" class="btn btn-danger btn-sm" onclick="delMessage('{{$var->message_id}}')">
					<i class=" fa fa-trash-o"></i>&nbsp;删除
				</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
   {{$list->links()}}
</div>