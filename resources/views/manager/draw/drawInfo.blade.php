@include('manager.layouts.common')
<style>
	.form-group{margin-bottom:5px;}
	.form-control{display:inline;}
</style>
<div class="box box-info" style="padding:15px;">
	<div class="box-body">
		{{Form::model($drawApply,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
		{{Form::hidden('draw_apply_id',$drawApply->draw_apply_id)}}
		{{Form::hidden('bill_no',$drawApply->bill_no)}}
		<div class="row">
			<div class="col-sm-6">
				<label>会员名称：{{$user->display_name??''}}</label>
				{{Form::hidden('member_agent_id',$drawApply->member_agent_id)}}
			</div>
			<div class="col-sm-6">
				<label>银行名称：{{config('enums.bank_code')[$bankAccount['bank_code']]}}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<label>会员姓名：{{$user->real_name??''}}</label>
			</div>
			<div class="col-sm-6">
				<label>银行户名：{{$bankAccount['bank_account_name']}}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<label>取款金额：{{$drawApply->draw_money}}</label>
			</div>
			<div class="col-sm-6">
				<label>取款账号：{{$bankAccount['bank_account_number']}}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<label>受理时间：{{$drawApply->accept_time}}</label>
			</div>
			<div class="col-sm-6">
				<label>取款时间：{{$drawApply->draw_time}}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<label>订单状态：{{config('enums.draw_status')[$drawApply->draw_status]??''}}</label>
			</div>
			<div class="col-sm-6">

			</div>
		</div>
		@if($drawApply->draw_status=='apply')
			<div class="row">
				<div class="col-sm-6">
					<label>订单处理：</label>
					{{Form::radio('draw_status','accept',true,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="audit">接受</label>&nbsp;&nbsp;
					{{Form::radio('draw_status','reject',false,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="reject">拒绝</label>&nbsp;&nbsp;
				</div>
				<div class="col-sm-6">
					<label>相关备注：</label>
					{{Form::text('description',null,['id'=>'description','class'=>'form-control','style'=>'width:70%;'])}}
				</div>
			</div>
		@endif
		@if($drawApply->draw_status=='accept')
			<div class="row">
				<div class="col-sm-6">
					<label>订单处理：</label>
					{{Form::radio('draw_status','local_draw',true,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="audit">手动出款</label>&nbsp;&nbsp;
					{{Form::radio('draw_status','reject',false,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="audit">拒绝出款</label>&nbsp;&nbsp;
				</div>
				<div class="col-sm-6">
					<label>相关备注：</label>
					{{Form::text('description',null,['id'=>'description','class'=>'form-control','style'=>'width:70%;'])}}
				</div>
			</div>
		@endif
		@if($drawApply->draw_status=='audit')
			<div class="row">
				<div class="col-sm-6">
					<label>订单处理：</label>
					{{Form::radio('draw_status','success',true,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="success">成功</label>&nbsp;&nbsp;
					{{Form::radio('draw_status','mysuccess',true,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="success">强制成功</label>&nbsp;
					{{Form::radio('draw_status','reject',false,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="reject">拒绝</label>&nbsp;&nbsp;
					{{Form::radio('draw_status','failed',false,['class'=>'minimal control-label','id'=>'draw_status'])}}
					<label for="reject">失败</label>&nbsp;&nbsp;
				</div>
				<div class="col-sm-6">
					<label>相关备注：</label>
					{{Form::text('description',null,['id'=>'description','class'=>'form-control','style'=>'width:70%;'])}}
				</div>
			</div>
		@endif
		{{Form::close()}}
		<div class="row">
			<div class="box-footer pull-right">
				@if($drawApply->draw_status=='apply'||$drawApply->draw_status=='audit'||$drawApply->draw_status=='accept')
					@if($drawApply->payment_platform_code=='CCEX')
					<button type="button" class="btn btn-success" id="btn-Update" onclick="postBankInfo('{{$drawApply->draw_apply_id}}')">
						CCEX 更新银行信息
					</button>
					@endif
					<button type="button" class="btn btn-info" id="btn-Update" onclick="postDrawApply()">
						提交更新
					</button>
				@endif
				<button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">
					关闭本页
				</button>
			</div>
		</div>
	</div>
</div>
<script>
    function postBankInfo(drawApplyId) {
        $.ajax({
            type: 'post',
            url: '/manager/rePostBankInfo',
            data: {drawApplyId: drawApplyId},
            dataType: 'json',
            headers: {"X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content")},
            success: function (data) {
                if(data.status == 0) {
                    layer.alert(data.msg, {icon: 1, closeBtn: 0},function(){
                       window.document.location.reload();
					});

                }else{
                    layer.alert(data.msg, {icon: 2, closeBtn: 0});
                }
            },
            error: function (data) {
                layer.alert('提交失败，请稍后再试！',{icon: 2, closeBtn: 0});
            }
        });
    }

    function postDrawApply() {
        draw_status = $("input[name='draw_status']:checked").val();
        if (draw_status != '') {
            if (draw_status == 'reject' && $("#description").val() == '') {
                layer.alert('请在相关备注输入拒绝原因!', {icon: 2, closeBtn: 1});
                return true;
            }
            $.ajax({
                type: 'post',
                url: '/manager/updateMemberDrawApplyInfo',
                data: $('#postForm').serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status == 0) {
                        layer.alert(data.msg, {icon: 1, closeBtn: 0}, function () {
                            layerCloseMe();
                            pages = window.parent.$("#CURRENT_PAGE").val();
                            window.parent.drawApplyList(pages);
                        });
                    } else {
                        layer.alert(data.msg, {icon: 2, closeBtn: 0});
                    }
                },
                error: function (data) {
                    layer.alert('提交失败，请稍后再试！', {icon: 2, closeBtn: 0});
                }
            });
        } else {
            layer.alert('请选择订单处理结果!', {icon: 2, closeBtn: 1});
        }
    }
</script>