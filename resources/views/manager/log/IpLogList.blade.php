<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th>序号</th>
        <th>IP地址</th>
        <th>域名</th>
        <th>注册次数</th>
        <th>登录失败次数</th>
        <th>最后记录时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ipLogs as $key=>$var)
        <tr>
            <td>{{++$key+($page_count*($page-1)) }}</td>
            <td>{{$var->ip_addr}}</td>
            <td>{{$var->domain}}</td>
            <td>{{$var->register_count}}</td>
            <td>{{$var->failed_count}}</td>
            <td>{{str_limit($var->record_time,32)}}</td>
            <td>
                <a href="#" class="btn btn-info btn-sm" onclick="ipReset('{{$var->id}}')">
                    重置归零</a>
	            @if($var->blackId=='')
                    <a href="#" class="btn btn-danger btn-sm" onclick="addIpBlack('{{$var->id}}','{{$var->blackId}}')">
	                   {{$var->blackId==''?'设置':'移除'}}黑名单</a>
				@else
		            <a href="#" class="btn btn-warning btn-sm" onclick="addIpBlack('{{$var->id}}','{{$var->blackId}}')">
	                   {{$var->blackId==''?'设置':'移除'}}黑名单</a>
				@endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
   {{$ipLogs->links()}}
</div>
