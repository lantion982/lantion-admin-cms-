{{Form::model(null,['class'=>'form-horizontal form-bordered','id'=>'searchForm','name'=>'searchForm' ])}}
<div class="row">
    <div class="col-lg-1 tabmin">
        <label for="page_count">会员帐号</label>
    </div>
    <div class="col-lg-2">
        {{form::text('keyword','',['id'=>'keyword','class'=>'form-control','placeholder'=>'账号、内容'])}}
    </div>
    <div class="col-lg-1 tabmin">
        <label for="page_count">每页显示</label>
    </div>
    <div class="col-lg-1 dtcol">
        <select class="form-control selectpicker" id="page_count" name="page_count">
            @foreach(config('enums.page_count') as $pval)
                <option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2">
        <a href="#" class="btn btn-danger" onclick="getFeedBack('1')" style="width:40%;">
			<i class="fa fa-search"></i>&nbsp;查询</a>
        <a href="#" class="btn btn-default" onclick="clearSearch();" style="width:40%;">
			<i class="fa fa-times"></i>&nbsp;清空</a>
    </div>
</div>
{{Form::close()}}
