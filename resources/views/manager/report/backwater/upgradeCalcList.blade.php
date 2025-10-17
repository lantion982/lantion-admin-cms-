<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>编号</th>
		@if(\App\Libs\Helper::isSuper())
			<th>公司</th>
		@endif
        <th>升级模式</th>
        <th>包括活动流水</th>
        <th>总礼金</th>
        <th>结算周期</th>
        <th>结算日期</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($upgradeBatches as $key=>$upgradeBatch)
        <tr>
            <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
			@if(\App\Libs\Helper::isSuper())
				<td>{{$upgradeBatch->company->name??'-'}}</td>
			@endif
	        <td>
				<a href="/manager/upgradeDetail?upgrade_batch_no={{$upgradeBatch->upgrade_batch_no}}" title="点击查看明细">
					{{$upgradeBatch->upgrade_batch_no}}
				</a>
			</td>
            <td>{{config('enums.upgrade_type')[$upgradeBatch->upgrade_type]??'-'}}</td>
            <td>{{$upgradeBatch->include_activity_water?'是':'否'}}</td>
            <td>{{number_format($upgradeBatch->gift_money_total,2)}}</td>
            <td>{!!$upgradeBatch->period!!}</td>
            <td>{{$upgradeBatch->calc_date}}</td>
            <td class="t-c">
                @if($upgradeBatch->is_given ==0)
                    <a href="javascript:" id="giveMoney{{$upgradeBatch->upgrade_batch_id}}" style="color:#0C0;"
						onclick="giveGiftMoneyByBatch('{{$upgradeBatch->upgrade_batch_id}}')">派发</a>
                @else
                    已经派发
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
    <div class="col-lg-12">
        {{$upgradeBatches->links()}}
    </div>
</div>