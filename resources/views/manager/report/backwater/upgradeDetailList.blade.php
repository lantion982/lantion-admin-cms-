@if(empty($upgradeBatch))
@else
    <div class="row" style="margin:0">
        <div class="col-lg-10 mb1" style="font-size:14px;">
            结算周期：{{$upgradeBatch->begin_date}}-{{$upgradeBatch->end_date}}&nbsp;&nbsp;
        </div>
    </div>
@endif
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="{{$page_count}}">
    <input type="hidden" id="CURRENT_PAGE" value="{{$current_page}}">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>账号</th>
        <th>有效流水</th>
        <th>存款量</th>
        <th>礼金</th>
        <th>类型</th>
        <th>是否派发</th>
        <th>升级前</th>
        <th>升级后</th>
        <th>日期</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($upgradeDetails as $key=>$upgradeDetail)
        <tr>
            <td class="t-c">{{++$key+($page_count*($current_page-1))}}</td>
            <td>{{$upgradeDetail->member->login_name??''}}</td>
            <td>{{$upgradeDetail->game_money_total?mynumber($upgradeDetail->game_money_total):'--'}}</td>
            <td>{{$upgradeDetail->deposit_total?mynumber($upgradeDetail->deposit_total):'--'}}</td>
            <td>{{mynumber($upgradeDetail->gift_money)}}</td>
            <td>{{$upgradeDetail->gift_type?'每月红包':'升级礼金'}}</td>
            <td>{{$upgradeDetail->is_given?'是':'否'}}</td>
            <td>{{$upgradeDetail->level_uid_oldn}}</td>
            <td>{{$upgradeDetail->level_uid_newn}}</td>
            <td>{{$upgradeDetail->calc_date}}</td>
            <td>
                @if($upgradeDetail->is_given == 0)
                    <a href="javascript:" id="giveMoney{{$upgradeDetail->upgrade_detail_id}}" style="color:#0C0;"
						onclick="giveGiftMoneyByDetail('{{$upgradeDetail->upgrade_detail_id}}')">派发</a>
                @else
                    已经派发
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@if($upgradeDetails)
	<div class="box-info">
		{{$upgradeDetails->links()}}
	</div>
@endif
