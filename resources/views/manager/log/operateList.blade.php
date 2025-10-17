<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th>序</th>
        <th>操作人</th>
        <th>时间</th>
        <th>操作内容</th>
    </tr>
    </thead>
    <tbody>
    @foreach($logOperations as $key=>$operateLog)
        <tr>
            <td>{{++$key+($page_count*($page-1))}}</td>
            <td>{{$operateLog->admin->login_name}}</td>
            <td>{{$operateLog->created_at}}</td>
            <td><abbr title="{{$operateLog->content}}">{{str_limit($operateLog->content,90)}}</abbr></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
       {{$logOperations->links()}}
    </div>
</div>
