<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
        <tr>
            <th>序</th>
            <th>类型</th>
            <th>帐号</th>
            <th>登录IP</th>
            <th>登录地区</th>
            <th>登录结果</th>
            <th>登录时间</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logLogins as $key=>$logLogin)
            <tr>
                <td>{{ ++$key+($page_count*($page-1)) }}</td>
                <td>{{config('enums.member_admin_type')[$logLogin->member_type]??''}}</td>
                <td>{{$logLogin->login_name}}</td>
                <td>{{$logLogin->login_ip}}</td>
                <td>{{$logLogin->login_area}}</td>
                <td class="@if($logLogin->login_result=='success') text-green @else text-red @endif">
                    @if($logLogin->login_result=='success') 成功 @else 失败 @endif
                </td>
                <td>{{$logLogin->created_at}}</td>

            </tr>
        @endforeach
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
        {{$logLogins->links()}}
    </div>
</div>
