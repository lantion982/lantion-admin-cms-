<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	{{Form::hidden('order_field', 'created_at')}}
	{{Form::hidden('order_type', 'desc')}}
	<input type="hidden" id="Rtype" name="Rtype" value="{{$type}}">
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<label for="company_id" class="hidden-sm hidden-xs">公司</label>
			@if(\App\Libs\Helper::isSuper())
				<select class="form-control selectpicker" id="company_id" name="company_id" data-live-search='true'
					title="公司">
					<option value="company_super">平台</option>
					@foreach($company as $key => $val)
						<option value="{{$key}}" @if($key==$company_id) selected @endif>{{$val}}</option>
					@endforeach
				</select>
			@else
				<select class="form-control selectpicker" id="company_id" name="company_id" disabled>
					<option value="{{\App\Libs\Helper::getCompanyId()}}">{{\App\Libs\Helper::getCompanyName()}}</option>
				</select>
			@endif
		</div>
		<div class="col-md-3 col-sm-6">
			<label for="keyword" class="hidden-sm hidden-xs">会员帐号</label>
			<input type="text" id="keyword" name="keyword" placeholder="会员帐号" class="form-control">
		</div>
		<div class="col-md-3 col-sm-6">
			<label for="deposit_status" class="hidden-sm hidden-xs">游戏大厅</label>
			<select class="form-control selectpicker" id="deposit_status" name="deposit_status" data-live-search='true'
				title="游戏大厅">
				<option value="">不限</option>
				@foreach(config('enums.room_code') as $key => $val)
					<option value="{{$key}}" @if($key==$room_code) selected @endif>{{$val}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-3 col-sm-6 hidden-xs">
			<label for="page_count" class="hidden-sm hidden-xs" style="margin-top:5px;">&nbsp;</label>
			<select class="form-control selectpicker" id="page_count" name="page_count">
				@foreach(config('enums.page_count') as $pval)
					<option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息/每页</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row">
		@include('manager.searchDate')
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<button type="button" class="form-control btn btn-info" onclick="getRoomUser('1');" id="submitSearch">
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
