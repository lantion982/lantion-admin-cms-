{!! Form::model(null, ['class' => 'form-horizontal form-bordered', 'id' => 'searchForm', 'name' => 'searchForm','method'=>'GET']) !!}
<!--2018-8-16 Toni Tang 新搜索开始-->
<!--第一行-->
<div class="row">
    <div class="col-lg-1 tabmin">
        <label>手机号码</label>
    </div>
    <div class="col-lg-2">
        <input type="text" id="keyword" name="keyword" placeholder="手机号码" class="form-control">
    </div>
    <div class="col-lg-1 tabmin">
        <label>开始时间</label>
    </div>
    <div class="col-lg-1 dtcol">
        <input name="startDate" id="startDate" class="form-control mr1 query_time" placeholder="开始时间" type="text" value="{{date('Y-m-01 00:00:00')}}">
    </div>
    <div class="col-lg-1 dwcol">
        <a href="#" class="btn btn-default" onclick="setdate(1,-1);" title="前一天"><i class="fa fa-angle-double-left"></i></a>
        <a href="#" class="btn btn-default" onclick="setdate(1,0);">今天</a>
        <a href="#" class="btn btn-default" onclick="setdate(1,1);" title="后一天"><i class="fa fa-angle-double-right"></i></a>
    </div>
    <div class="col-lg-1 dwcol">
        <input id="addmonth" type="hidden" value="0">
        <a href="#" class="btn btn-default" onclick="setdate(3,-1);" title="上个月"><i class="fa fa-angle-double-left"></i></a>
        <a href="#" class="btn btn-default" onclick="setdate(3,0);">本月</a>
        <a href="#" class="btn btn-default" onclick="setdate(3,1);" title="下个月"><i class="fa fa-angle-double-right"></i></a>
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-danger form-control" onclick="getLogViewSMS(1);"><i class="fa fa-search"></i> 查询</a>
    </div>
</div>
<!--第二行-->
<div class="row">
    <div class="col-lg-1 tabmin">
        <label for="page_count">每页显示</label>
    </div>
    <div class="col-lg-2">
        <select class="form-control selectpicker" id="page_count" name="page_count">
            @foreach(config('enums.page_count') as $pval)
                <option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-1 tabmin">
        <label>结束时间</label>
    </div>
    <div class="col-lg-1 dtcol">
        <input name="endDate" id="endDate" class="form-control query_time" placeholder="结束时间" type="text" value="{{date('Y-m-d 23:59:59')}}">
    </div>
    <div class="col-lg-1 dwcol">
        <input id="addweek" type="hidden" value="0">
        <a href="#" class="btn btn-default" onclick="setdate(2,-1);" title="上一周"><i class="fa fa-angle-double-left"></i></a>
        <a href="#" class="btn btn-default" onclick="setdate(2,0);">本周</a>
        <a href="#" class="btn btn-default" onclick="setdate(2,1);" title="下一周"><i class="fa fa-angle-double-right"></i></a>
    </div>
    <div class="col-lg-1 dwcol">
        <input id="addmonth" type="hidden" value="0">
        <a href="#" class="btn btn-default" onclick="setdate(4,-1);" title="去年"><i class="fa fa-angle-double-left"></i></a>
        <a href="#" class="btn btn-default" onclick="setdate(4,0);">本年</a>
        <a href="#" class="btn btn-default" title="下一年" disabled><i class="fa fa-angle-double-right"></i></a>
    </div>
    <div class="col-lg-1">
        <a href="#" class="form-control btn btn-default" onclick="clearForm();" style="margin-top:1px;"><i class="fa fa-times"></i> 清空</a>
    </div>

</div>
<!--第三行-->
@include('manager.searchScript')
<!--2018-8-16 Toni Tang 新搜索结束-->
{!! Form::close() !!}