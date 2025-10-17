<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page"  name='page' value="{{$page}}">
    <thead>
    <tr>
        <th>序号</th>
        <th>反馈时间</th>
        <th>会员账号</th>
        <th>反馈内容</th>
    </tr>
    </thead>
    <tbody>
    @foreach($feedBack as $key=>$back)
        <tr>
            <td>{{++$key+(20*($page-1))}}></td>
            <td>{{$back->created_at}}</td>
            <td>{{$back->login_name}}</td>
            <td>{{$back->content}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
        {{$feedBack->links()}}
    </div>
</div>
