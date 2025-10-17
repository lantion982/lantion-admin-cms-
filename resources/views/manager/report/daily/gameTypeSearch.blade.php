<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
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
					<option value="{{$company_id}}">{{\App\Libs\Helper::getCompanyName()}}</option>
				</select>
			@endif
		</div>
		<div class="col-md-3 col-sm-6">
			<label for="game_type_code" class="hidden-sm hidden-xs">游戏类型</label>
			<select class="form-control selectpicker" id="game_type_code" name="game_type_code" title="游戏类型">
				@foreach($game_type as $key => $val)
					<option value="{{$key}}" @if($game_type_code==$key) selected @endif>{{$val}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
			<label for="page_count" class="hidden-sm hidden-xs" style="margin-top:3px;">&nbsp;</label>
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
			<button type="button" class="form-control btn btn-info" onclick="getGameTypes('1');" id="submitSearch">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-3 col-sm-6">
			<a href="#" class="form-control btn btn-danger" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</a>
		</div>
	</div>
	@include('manager.searchScript')
</form>
