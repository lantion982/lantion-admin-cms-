<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<label for="company_id" class="hidden-sm hidden-xs">公司</label>
			@if(\App\Libs\Helper::isSuper())
				<select class="form-control selectpicker" id="company_id" name="company_id" data-live-search='true'
					title="所属公司">
					<option value="company_super" @if($company_id=='company_super') selected @endif>平台</option>
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
			<label for="deposit_status" class="hidden-sm hidden-xs">游戏大厅</label>
			<select class="form-control selectpicker" id="deposit_status" name="deposit_status"
				data-live-search='true' title="游戏大厅">
				<option value="">不限</option>
				@foreach(config('enums.room_code') as $key => $val)
					<option value="{{$key}}" @if($key==$room_code) selected @endif>{{$val}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
			<label for="is_locked" class="hidden-sm hidden-xs">是否锁定</label>
			<select name="is_locked" id="is_locked" class="form-control selectpicker">
				<option value="">不限</option>
				<option value="1">是</option>
				<option value="0">否</option>
			</select>
		</div>
	</div>
	<div class="row">
		@include('manager.searchDate')
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<button type="button" class="form-control btn btn-info" onclick="getRoomDaily('1');" id="submitSearch">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-3 col-sm-6">
			<button type="button" class="form-control btn btn-danger" id="btnClear" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
	@include('manager.searchScript')
</form>
