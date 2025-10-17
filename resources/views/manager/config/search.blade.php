{{Form::model(null,['class'=>'form-horizontal form-bordered','id'=>'searchForm','name'=>'searchForm','method'=>'GET'])}}
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
        <label>关键字</label>
    </div>
    <div class="col-lg-2">
        <input type="text" id="keyword" name="keyword" placeholder="IP|主机地址" class="form-control">
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-info form-control" onclick="getIP(1)"><i class="fa fa-search"></i> 查询</a>
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-danger form-control" onclick="clearForm();" style="margin-top:1px;">
            <i class="fa fa-times"></i> 清空</a>
    </div>
</div>
@include('manager.searchScript')
{{Form::close()}}
