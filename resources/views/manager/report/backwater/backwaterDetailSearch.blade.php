{!! Form::model(null, ['class' => 'form-horizontal form-bordered', 'id' => 'searchForm', 'name' => 'searchForm','method'=>'GET']) !!}
<!--2018-8-17 Toni Tang 新搜索开始-->
<div class="row">
    <div class="col-lg-1 tabmin">
        <label for="keyword">会员账号</label>
    </div>
    <div class="col-lg-2">
        <input name="keyword" id="keyword" class="form-control" placeholder="会员账号，支持模糊查询" type="text">
    </div>
    <div class="col-lg-1 tabmin">
        <label>结算编号</label>
    </div>
    <div class="col-lg-1 dtcol">
        {!!Form::text('backwater_batch_no', empty(request()['backwater_batch_no'])?'':request()['backwater_batch_no'],['id' => 'backwater_batch_no','class'=>'form-control','placeholder'=>'结算编号'])!!}
    </div>
    <div class="col-lg-1 tabmin">
        <label for="page_count">每页显示</label>
    </div>
    <div class="col-lg-1 dwcol">
        <select class="form-control selectpicker" id="page_count" name="page_count">
            @foreach(config('enums.page_count') as $pval)
                <option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-1 dwmol">
        <a href="#" class="btn btn-danger form-control" onclick="getBackWaterDetail('1');" id="submitSearch">
            <i class="fa fa-search"></i>&nbsp;查询</a>
    </div>
    <div class="col-lg-1 dwmol">
        <a href="#" class="btn btn-default form-control" onclick="clearForm();" >
            <i class="fa fa-times"></i>&nbsp;清空</a>
    </div>
</div>
@include('manager.searchScript')
<!--2018-8-17 Toni Tang 新搜索结束-->
{{Form::close()}}
