<form class="form-horizontal form-bordered" id="searchForm" name="searchForm" method="GET">
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<label for="company_id" class="hidden-sm hidden-xs">所属公司</label>
			@if(\App\Libs\Helper::isSuper())
				<select class="form-control selectpicker" id="company_id" name="company_id" title="选择公司"
					data-live-search='true'>
					@foreach($company as $key => $val)
						<option value="{{$key}}" @if($val=='全部') selected @endif> {{$val}}</option>
					@endforeach
				</select>
			@else
				<select class="form-control selectpicker" id="company_id" name="company_id" disabled>
					<option value="{{\App\Libs\Helper::getCompanyId()}}">本公司</option>
				</select>
			@endif
		</div>
		<div class="col-md-3 col-sm-6">
			<label class="hidden-sm hidden-xs" for="login_name">会员帐号</label>
			<input name="login_name" id="login_name" class="form-control" placeholder="会员帐号"
				type="text" value="{{$login_name}}" autocomplete="off">
		</div>
		<div class="col-md-3 col-sm-6">
			<label class="hidden-sm hidden-xs" for="upgrade_batch_no">结算条目编号</label>
			<input name="upgrade_batch_no" id="upgrade_batch_no" class="form-control" placeholder="结算条目编号"
				type="text" value="{{$upgrade_batch_no}}" autocomplete="off">
		</div>
		<div class="col-md-3 col-sm-6 hidden-xs">
			<label for="page_count" class="hidden-sm hidden-xs">每页显示</label>
			<select class="form-control selectpicker" id="page_count" name="page_count">
				@foreach(config('enums.page_count') as $pval)
					<option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<button type="button" class="btn btn-info form-control" onclick="getUpgradeDetail(1)">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-3 col-sm-6">
			<button type="button" class="btn btn-danger form-control" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
</form>