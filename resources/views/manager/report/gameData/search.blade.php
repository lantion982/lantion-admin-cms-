<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
{!!Form::hidden('order_field', 'created_at')!!}
<!--游戏单号-->
<!--第一行-->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <label for="company_id">所属公司</label>
		@if(\App\Libs\Helper::isSuper())
			<select class="form-control selectpicker" id="company_id" name="company_id" data-live-search='true'
				title="请选择所属公司">
				<option value="company_super">平台</option>
				@foreach($company as $key=>$val)
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
        <label for="keyword">会员帐号</label>
        <input type="text" id="keyword" name="keyword" placeholder="会员帐号、姓名、手机号、QQ、微信、域名"
               class="form-control" value="{{$keyword}}">
    </div>
    <div class="col-md-3 col-sm-6">
        <label for="room_code">游戏大厅</label>
        <select class="form-control selectpicker" id="room_name" name="room_code" title="不限">
            @foreach($rooms as $key => $val)
                <option value="{{$key}}" @if($key==$room_code) selected @endif>{{$val}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 col-sm-6">
        <label>游戏类型</label>
        <select class="form-control selectpicker" id="game_type_code" name="game_type_code" title="不限">
            @foreach(config('enums.game_type_code') as $key => $val)
                <option value="{{$key}}">{{$val}}</option>
            @endforeach
        </select>
    </div>
</div>
<!--第二行-->
<div class="row">
	@include('manager.searchDateTime')
</div>
<!--第三行-->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <input type="text" id="billNo" name="billNo" placeholder="请输入要查询的订单号" class="form-control">
    </div>
    <div class="col-md-3 col-sm-6">
        <input type="text" id="agent_name" name="agent_name" placeholder="代理帐号" class="form-control">
    </div>
	<div class="col-md-3 col-sm-6">
		<select class="form-control selectpicker" id="page_count" name="page_count">
			@foreach(config('enums.page_count') as $pval)
				<option value="{{$pval}}" @if($page_count==$pval) selected @endif>{{$pval}}条信息/每页</option>
			@endforeach
		</select>
	</div>
    <div class="col-md-3 col-sm-6">
		<a href="#" class="btn btn-info form-control" onclick="getGameData('1');" id="submitSearch" style="width:45%;">
			<i class="fa fa-search"></i>&nbsp;查询
		</a>
        <a href="#" class="form-control btn btn-danger" onclick="clearForm();" style="width:45%;">
            <i class="fa fa-times"></i>&nbsp;清空
        </a>
    </div>

</div>
@include('manager.searchScript')
</form>
