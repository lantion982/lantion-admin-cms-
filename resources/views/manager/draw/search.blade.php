<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
{{Form::hidden('user_type',$user_type,['id'=>'user_type'])}}
<div class="row">
	<div class="col-md-3 col-sm-6">
		<label for="company_id" class="hidden-sm hidden-xs">公司</label>
		@if(\App\Libs\Helper::isSuper())
			<select class="form-control selectpicker" id="company_id" name="company_id" data-live-search='true'>
				@foreach($company as $key=>$val)
					<option value="{{$key}}" @if($val=='全部') selected @endif>{{$val}}</option>
				@endforeach
			</select>
		@else
			<select class="form-control selectpicker" id="company_id" name="company_id" disabled>
				<option value="{{\App\Libs\Helper::getCompanyId()}}">{{\App\Libs\Helper::getCompanyName()}}</option>
			</select>
		@endif
	</div>
    <div class="col-md-3 col-sm-6">
        <label class="hidden-sm hidden-xs">会员信息</label>
        <input type="text" id="keyword" name="keyword" placeholder="账号|姓名|手机号|微信|域名" class="form-control">
    </div>
	<div class="col-md-3 col-sm-6">
		<label for="billNo" class="hidden-sm hidden-xs">订单编号</label>
		<input type="text" id="billNo" name="billNo" placeholder="请输入要查询的订单号" class="form-control">
	</div>
	<div class="col-md-3 col-sm-6">
		<label class="hidden-sm hidden-xs">订单状态</label>
		{{Form::select('deposit_status[]',$draw_status,null,['id'=>'deposit_status','class'=>'form-control selectpicker','multiple',])}}
	</div>

</div>
<div class="row">
    @include('manager.searchDateTime')
</div>
<div class="row">
	<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
		<select class="form-control selectpicker" id="page_count" name="page_count">
			@foreach(config('enums.page_count') as $pval)
				<option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息/每页</option>
			@endforeach
		</select>
	</div>
	<div class="col-md-3 col-sm-6">
		<button type="button" class="btn btn-info form-control" onclick="drawApplyList(1)" id="submitSearch">
			<i class="fa fa-search"></i>&nbsp;查询
		</button>
	</div>
	<div class="col-md-3 col-sm-6">
		<button type="button" class="form-control btn btn-danger" onclick="clearForm();">
			<i class="fa fa-times"></i>&nbsp;清空
		</button>
	</div>
</div>
@include('manager.searchScript')
</form>
