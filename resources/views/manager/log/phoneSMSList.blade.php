<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{!! $page_count !!}">
    <input type="hidden" id="CURRENT_PAGE" value="{!! $current_page !!}">
    <thead>
    <tr>
        <th>序</th>
        <th>手机号码</th>
        <th>验证码</th>
        <th>发送时间</th>
        <th>过期时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tempPhone as $key=>$phone)
        <tr>
            <td>{{ ++$key+($page_count*($current_page-1)) }} <input type="hidden" name="orderId" value="{{ $phone->log_phone_sms_id }}">
                <input type="hidden" name="orderNum"  value="{{ ++$key+($page_count*($current_page-1)) }}"/>
            </td>
            <td>{!! $phone->phone !!}</td>
            <td>{!! $phone->code !!}</td>
            <td>{!! $phone->time_send !!}</td>
            <td>{!! $phone->time_out !!}</td>
            <td>
                <a href="javascript:void(0);" class="btn btn-default btn-sm" onclick="logViewSMSInfo('{!! $phone->log_phone_sms_id !!}','{!! $phone->phone !!}')"><i class="fa fa-pencil"></i> 编辑</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<!--2018-8-16 Toni Tang -->
<div class="box-info">
    <div class="col-lg-12">
       {!! $tempPhone->links() !!}
    </div>
</div>