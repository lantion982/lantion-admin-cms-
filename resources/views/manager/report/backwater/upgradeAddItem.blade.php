@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">添加升级条目</h5>
	                <div class="box-tools pull-right">
		                <button type="button" title="刷新" class="btn btn-box-tool" onclick="location.reload();">
							<i class="fa fa-refresh"></i>
						</button>
	                    <button type="button" title="展开&收缩" class="btn btn-box-tool" data-widget="collapse">
							<i class="fa fa-minus"></i>
						</button>
	                </div>
	            </div>
	            <div class="box-body">
	                <div class="row">
		                @include('manager.report.backwater.backwater-top-menu')
	                </div>
		            <div class="row">
			            <div id="ajaxContent"  class="box-body table-responsive no-padding">
							<form class="form-horizontal form-bordered" id="itemForm" name="itemForm" method="get">
								<div class="row">
									<div class="col-sm-4">
										<label for="time1">开始日期：</label>
										<input name="time1" id="time1" class="form-control query_date" type="text"
											value="{{\Carbon\Carbon::now()->format('Y-m-d')}}">
									</div>
									<div class="col-sm-4">
										<label for="time2">结束日期：</label>
										<input name="time2" id="time2" class="form-control query_date" type="text"
											value="{{\Carbon\Carbon::now()->format('Y-m-d')}}">
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<label>升级模式：</label>
										{{Form::radio('upgrade_type','net_amount_and_deposit','net_amount_and_deposit',['class'=>'mt1'])}} 流水和存款(两者都满足)&nbsp;&nbsp;
										{{Form::radio('upgrade_type','net_amount_or_deposit',null,['class'=>'mt1'])}} 流水或存款(两者满足其一)&nbsp;&nbsp;
										{{Form::radio('upgrade_type','net_amount',null,['class'=>'mt1'])}} 流水量&nbsp;&nbsp;
										{{Form::radio('upgrade_type','deposit',null,['class'=>'mt1'])}} 存款量&nbsp;&nbsp;
									</div>
								</div>
								<div class="row">
									<div class="col-sm-6">
										<label>包括活动流水：</label>
										{{Form::radio('include_activity_water',1,null,['class'=>'mt1'])}}&nbsp;是&nbsp;&nbsp;
										{{Form::radio('include_activity_water',0,true,['class'=>'mt1'])}}&nbsp;否&nbsp;&nbsp;
									</div>
								</div>
								<div class="row" style="margin:20px 0;">
									<div class="col-sm-10">
										<button type='button' class="btn btn-info" onclick="upgradeAdd();"
											style="width:200px;" id="btnUpgradeAdd" name="btnUpgradeAdd">
											提交添加条目</button>
									</div>
								</div>
							</form>
				        </div>
		            </div>
	            </div>
	        </div>
	    </div>
	</div>
    <script type="text/javascript" src="{{'/js/page/report/backwater/upgradeItem.js?'.time()}}"></script>
@endsection
