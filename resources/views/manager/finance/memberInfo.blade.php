@extends('manager.superUI')
@section('content')
	<script type="text/javascript" src="{{'/js/page/finance/memberInfoCommit.js?'.time()}}"></script>
	<div class="box box-info">
		<form id="updateMemberAccountInfoCommitForm" method="post">
			<input type="hidden" value="{{$member->member_id}}" id="member_id" name="member_id">
			<div class="row">
				<div class="col-sm-4 col-xs-6">
					<label>所属代理：{{$member->agent->login_name??'-'}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>注册域名：{{$member->register_domain}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>会员账号：{{$member->display_name}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>真实姓名：{{$member->real_name}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>会员生日：{{$member->birthday}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>所属分组：{{$groups[$member->group_id]??''}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>钱包余额：{{$member->balance}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>累计取款：{{$member->total_draw}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label for="failed">交易失败：{{$member->trade_failed_count}}</label>
				</div>
				<div class="col-sm-4 col-xs-6">
					<label>冻结金额：{{$member->freeze_draw}}</label>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4 col-xs-6">
					<label for="is_allow_draw">取款状态：</label>
					{{Form::checkbox('is_allow_draw',$member->member_id,$member->is_allow_draw,['class'=>'switch'])}}
				</div>
				{{--<div class="col-sm-4 col-xs-6">
					<a class="btn btn-info" href="javascript:" onclick="getBankAccounts('{{$member->member_id}}')">
						银行卡信息
					</a>
				</div>--}}
			</div>
		</form>
		<div class="row">
			<div class="col-sm-4 col-xs-6">
				<label class="control-label">合计金额：</label>
				<div class="atsin" id="showMoneysTotal">
					{{sprintf('%.2f',$member->balance)}}
				</div>
			</div>
		</div>
	</div>
@endsection