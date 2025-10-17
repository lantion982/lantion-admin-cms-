@extends('manager.superUI')
@section('content')

<table id="tbl-activities" class="table table-hover">
    @if($data==null)
        <h3>还没有该会员的操作记录！</h3>
        @endif
    <thead>
    <tr>
        @if($data!=null)
        会员__
            {{$data[0]['admin']->login_name}}
        __的操作日志列表
    </tr>
    <tr>
        <th>序号</th>
        <th>操作人</th>
        <th>操作内容</th>
        <th>操作时间</th>
    </tr>
    </thead>
    @endif
    <tbody>
    @if($data!=null)
    @foreach($data as $key => $v)
        <tr>
            <td>{{ $key+1}}</td>
            <td>{{ $v['admin']['login_name']}}</td>
            <td>{{ $v['content']}}</td>
            <td>{{ $v['operating_time']}}</td>
        </tr>
    @endforeach
    @endif
    </tbody>
</table>
<style>
    #tbl-activities{
        background-color:#FFFFFF;
    }
</style>
    @if($data!=null)
{{ $data->appends('admin_id',$admin_id)->links() }}
    @endif
@endsection