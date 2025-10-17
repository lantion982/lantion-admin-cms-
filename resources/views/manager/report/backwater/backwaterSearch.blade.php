{{Form::model(null,['class'=>'form-horizontal form-bordered','id'=>'searchForm','name'=>'searchForm','method'=>'GET'])}}
<!--会员返水批次-->
<div class="row">
    @if(isset($isSuperBatch))
        <input id="isSuperBatch" name="isSuperBatch" value="{{$isSuperBatch}}" type="hidden">
    @endif
    @include('manager.searchDateTime')
</div>
<div class="row">
    <div class="col-md-3 col-sm-6">
        <a href="#" class="btn btn-info form-control" onclick="getBackWaterCalc('1');" id="submitSearch">
            <i class="fa fa-search"></i>&nbsp;查询
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="#" class="btn btn-danger form-control" onclick="clearForm();" >
            <i class="fa fa-times"></i>&nbsp;清空
        </a>
    </div>
</div>

@include('manager.searchScript')
{{Form::close()}}