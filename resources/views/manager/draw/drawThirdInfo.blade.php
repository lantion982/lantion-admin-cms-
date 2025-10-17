@include('manager.layouts.common')
<style>
	.form-group{margin-bottom:5px;}
	.form-control{display:inline;}
</style>
<div class="box box-info" style="padding:15px;">
	<div class="box-body">
		<form name="thirdWithdrawalForm" id="thirdWithdrawalForm" method="post">
			{{Form::hidden('draw_apply_id',$drawApply->draw_apply_id)}}
			<div class="row">
				<div class="col-xs-12">
					<label>出款方式：</label>
					@foreach($payments as $key=>$val)
						{{Form::radio('payment_account_id',$val->payment_account_id,false,['class'=>'flat-red','id'=>'payee_out_'.$key])}}
						<label for='payee_out_{{$key}}'>{{$val->account_name}}-出款</label>
					@endforeach
				</div>
				<hr>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label>会员账号：{{$login_name}}</label>
				</div>
				<div class="col-sm-6">
					<label>取款金额：{{$drawApply->draw_money}}</label>
				</div>
				<div class="col-sm-6">
					<label>银行名称：{{$bankAccount->bank->bank_name}}</label>
				</div>
				<div class="col-sm-6">
					<label>开户城市：{{$bankAccount->opening_address}}</label>
				</div>
				<div class="col-sm-6">
					<label>账户姓名：{{$bankAccount->bank_account_name}}</label>
				</div>
				<div class="col-sm-6">
					<label>银行卡号：{{$bankAccount->bank_account_number}}</label>
				</div>
			</div>
		</form>
		<div class="box-footer  pull-right">
			<button type="button" class="btn btn-info" id="btn-Update" onclick="postDrawApply()">确认出款</button>
			<button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
		</div>
	</div>

</div>

<script>
    function postDrawApply() {
        draw_status = $("input[name='payment_account_id']:checked").val();
        if (draw_status == null) {
            layer.alert('请选择处款方式！', {icon: 2, closeBtn: 1});
            return false;
        }
        var index = layer.open({
            title: '温馨提示',
            content: '确认要出款吗？',
            btn: ['确定', '取消'],
            closeBtn: 0,
            yes: function () {
                layer.close(index);
                layer.load(1, {shade: [0.4, '#f4f4f4 ']});
                $.ajax({
                    type: 'post',
                    url: '/manager/subMemberWithdrawalThird',
                    data: $('#thirdWithdrawalForm').serialize(),
                    dataType: 'json',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function (data) {
                        var index2 = layer.open({
                            title: '温馨提示',
                            content: data.msg,
                            btn: '确定',
                            icon: 1,
                            closeBtn: 0,
                            yes: function () {
                                layerCloseMe();
                            }
                        });
                        window.parent.drawApplyList($('#CURRENT_PAGE', window.parent.document).val());
                    },
                    error: function (data) {
                        var index2 = layer.open({
                            title: '温馨提示',
                            content: '网络连接失败，请刷新后重试！',
                            btn: '确定',
                            icon: 2,
                            closeBtn: 0,
                            yes: function () {
                                layerCloseMe();
                            }
                        });
                    }
                });
            },
            btn2: function () {

            }
        });
    }
</script>