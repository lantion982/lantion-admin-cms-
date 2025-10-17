@include('manager.layouts.common')
<form id="searchForm" name="searchForm" class='form-horizontal form-bordered' method="get" action="{{route('getMemberLoginLog')}}">
    <input id="id" name="id" value="{{$member_id}}" type="hidden">
    <div class="row">
        <div class="col-sm-3">
            <label for="keyword" class="hidden-sm hidden-xs">IP</label>
            <input type="text" id="keyword" name="keyword" placeholder="IP" class="form-control" value="{{$keyword}}">
        </div>
        <div class="col-sm-3">
            <label class="hidden-sm hidden-xs">开始时间</label>
            <input name="startDate" id="startDate" class="form-control query_time" placeholder="开始时间" type="text" value="{{$startDate}}">
        </div>
        <div class="col-sm-3">
            <label class="hidden-sm hidden-xs">结束时间</label>
            <input name="endDate" id="endDate" class="form-control query_time" placeholder="结束时间" type="text" value="{{$endDate}}">
        </div>
        <div class="col-sm-3">
            <button type="submit" class="form-control btn btn-info" id="submitSearch" style="width:48%;">
                <i class="fa fa-search"></i>&nbsp;查询
            </button>
            <button type="button" class="form-control btn btn-danger" onclick="clearForm();" style="width:48%;">
                <i class="fa fa-times"></i>&nbsp;清空
            </button>
        </div>
    </div>
</form>
<div class="box box-info" style="padding:15px;">
    <div class="box-body">
        <table id="tbl-activities" class="table table-hover" style="font-size:14px;">
            <thead>
                <tr>
                    <th>帐号</th>
                    <th>姓名</th>
                    <th>注册IP</th>
                    <th>注册地区</th>
                    <th>注册时间</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{$logReg->login_name}}</td>
                    <td>{{$logReg->nick_name}}</td>
                    <td>{{$logReg->register_ip}}</td>
                    <td>{{$logReg->register_area}}</td>
                    <td>{{$logReg->register_time}}</td>
                </tr>
            </tbody>
        </table>

        <table id="tbl-activities" class="table table-hover" style="margin-top:15px;font-size:14px;">
            <thead>
                <tr>
                    <th>帐号</th>
                    <th>登录IP</th>
                    <th>登录地区</th>
                    <th>登录结果</th>
                    <th>登录时间</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loginLogs as $key=>$log)
                <tr>
                    <td>{{$log->Member->login_name??$logReg->login_name}}</td>
                    <td>{{$log->login_ip}}</td>
                    <td>{{$log->login_area}}</td>
                    <td>{{config('enums.login_result')[$log->login_result]??'-'}}</td>
                    <td>{{$log->record_time}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="box-info">
            {{$loginLogs->links()}}
        </div>
    </div>

</div>
<script>
    function clearForm(){
        $('#keyword').val('');
        $('#startDate').val('{{date('')}}');
        $('#endDate').val('{{date('')}}');
    }

    $(document).ready(function(){
        dataTables();
        lay('.query_time').each(function(){
            laydate.render({
                elem:this
                ,type:'datetime'
                ,trigger:'click'
            });
        });
        lay('.query_date').each(function(){
            laydate.render({
                elem:this
                ,trigger:'click'
            });
        });
        $(document).keypress(function(e){
            if(!e){
                e = window.event;
            }
            if((e.keyCode||e.which)==13){
                $('#submitSearch').click();
                return false;
            }
        });
    });
</script>
