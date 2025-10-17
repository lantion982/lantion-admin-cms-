<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>公司</th>
        <th>编号(点击查看详情)</th>
        <th>结算日期</th>
        <th>结算周期</th>
        <th>总返水</th>
        <th>总结算返水</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($backwaterBatches as $key=>$backwaterBatch)
        <tr>
            <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
            <td>
                @if($backwaterBatch->company_id=='company_super')
                    平台
                @else
                    {{$backwaterBatch->company->name??'-'}}
                @endif
            </td>
            @if($backwaterBatch->company_id!='company_super')
              <td>
				  <a href="/manager/backwaterDetail?backwater_batch_no={{$backwaterBatch->backwater_batch_no}}">
					  {{$backwaterBatch->backwater_batch_no}}</a>
            @else
                <td>
					<a href="/manager/backwaterBatchCompany?backwater_batch_no={{$backwaterBatch->backwater_batch_no}}">
						{{$backwaterBatch->backwater_batch_no}}</a>
            @endif
            <td>{{$backwaterBatch->calc_date}}</td>
            <td>{!! $backwaterBatch->period !!}</td>
            <td>{{$backwaterBatch->waterback_money_total}}</td>
            <td>{{$backwaterBatch->waterback_money_calc}}</td>
            <td class="t-c">
                @if($backwaterBatch->is_locked=='1')
                    已锁
                @else
                    <a href="javascript:" id="backwaterLock{{$backwaterBatch->backwater_batch_id}}" style="color:#0C0;"
						onclick="backwaterLock('{{$backwaterBatch->backwater_batch_id}}')">锁定</a>&nbsp;|&nbsp;
                    @if($backwaterBatch->settle_status <> 'settled')
                        <a href="javascript:" id="backwaterRecycle{{$backwaterBatch->backwater_batch_id}}" style="color:#0C0;"
							onclick="backwaterSettle('all','{{$backwaterBatch->backwater_batch_no}}','999')">全部结算</a>&nbsp;|&nbsp;
                    @else
                        <a href="javascript:" id="backwaterRecycle{{$backwaterBatch->backwater_batch_id}}" style="color:#F00;"
							onclick="backwaterRecycle('all','{{$backwaterBatch->backwater_batch_no}}','999')">全部回收</a>
                    @endif
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
        {{$backwaterBatches->links()}}
    </div>
</div>