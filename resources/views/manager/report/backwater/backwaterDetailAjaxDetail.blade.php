@if(!empty($backwaterBatch))
	<div class="row" style="margin:0">
	    <div class="col-lg-10 mb1" style="font-size:14px;">
		    结算周期：{{$backwaterBatch->begin_date}} - {{ $backwaterBatch->end_date}}&nbsp;&nbsp;&nbsp;&nbsp;
			有效投注合计：<span style="color:red">{{mynumber($totalMoneyUse)}}</span>&nbsp;&nbsp;&nbsp;&nbsp;
			未结算总额：<span style="color:red">{{mynumber($totalWaterBackMoney)}}</span>&nbsp;&nbsp;&nbsp;&nbsp;
		    @if(\App\Libs\Helper::isSuper())
				<a onclick="rebuildDetail('{{$backwaterBatch->begin_date}}')" href="javascript:">检查漏掉数据</a>
			@endif
	    </div>
	</div>
@endif

<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th>序</th>
        <th>账号</th>
        <th>有效投注</th>
        <th>活动扣除流水</th>
        <th>返水</th>
        <th>结算日期</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($backwaterDetails as $key=>$backwaterDetail)
        <tr>
            <td>{{++$key+($page_count*($current_page-1))}}</td>
			<td>
				@if(\App\Libs\Helper::isSuper())
					{{$backwaterDetail->member->login_name??''}}
				@else
					{{$backwaterDetail->member->display_name??''}}
				@endif
			</td>
			<td>
				<a href="javascript:" onclick="moneysum('{{$backwaterDetail->member->display_name??''}}','{{$backwaterDetail->date}}')">
					{{$backwaterDetail->money_use}}
				</a>
			</td>
			<td>
				<a href="javascript:" onclick="reCheck('{{$backwaterDetail->member->member_id}}','{{$backwaterDetail->date}}')">
					{{$backwaterDetail->deduct_total}}
				</a>
			</td>
			<td>{{$backwaterDetail->backwater_refund}}</td>
			<td>{{$backwaterDetail->calc_date}}</td>
			<td>{{config('enums.settle_status')[$backwaterDetail->settle_status]}}</td>
            <td>
                @if($backwaterDetail->settle_status <> 'settled')
                    <a href="javascript:" id="backwaterRecycle{{$backwaterDetail->backwater_batch_id}}"
						style="color:#0C0;"  onclick="backwaterSettle('single','{{$backwaterDetail->backwater_batch_no}}','{{$backwaterDetail->backwater_detail_id}}')">结算</a>
                @else
                    <a href="javascript:" id="backwaterRecycle{{$backwaterDetail->backwater_batch_id}}"
						style="color:#F00;"  onclick="backwaterRecycle('single','{{$backwaterDetail->backwater_batch_no}}','{{$backwaterDetail->backwater_detail_id}}')">回收</a>
                @endif
            </td>
    	</tr>
    @endforeach

    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
        {{$backwaterDetails->links()}}
    </div>
</div>
<script>
    function rebuildDetail(date){
        $.ajax({
            type: 'get',
            url: '/manager/rebuildDetail?dates='+date,
            data: '',
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg,{icon:1,closeBtn:1},function(){
                        layer.closeAll();
                        getBackWaterDetail(1);
                    });
                }
            },
            error: function (data) {
                layer.alert('网络连接失败！',{icon:2,closeBtn:1});
                layer.closeAll();
            }
        });
    }
</script>