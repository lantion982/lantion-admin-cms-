@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">添加洗码结算条目</h5>
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
							<div class="row" style="padding-left:30px;">
								<div class="col-lg-12" >
									<div class="form-group">
										请确认每日报表中的有效投注正确，再进行条目添加；添加条目后,确认金额无误,即可点击结算
										<br/><br />
									</div>
								</div>
							</div>
							<form name="itemForm" id="itemForm" class="form-horizontal form-bordered" method="GET">
								<div class="row" style="margin:0;">
									<div class="col-lg-1" style="text-align:right">
										{{Form::label('labelForBegin','结算日期：',['class'=>'mt1'])}}
									</div>
									<div class="col-lg-2">
										<input name="time1" id="time1" class="form-control date_time1" type="text"
											value="{{\Carbon\Carbon::yesterday()->format('Y-m-d')}}" readonly="readonly">
										<input type="hidden" id="time2" value="">
									</div>
									<div class="col-lg-2" >
										<button type="button" class="btn btn-info ml5" id="btn-backwaterAdd"
											onclick="backwaterAdd();" style="width:200px;">提交&添加条目</button>
									</div>

								</div>
							</form>
				        </div>
		            </div>
	            </div>
	        </div>
	    </div>
	</div>
    <script type="text/javascript" src="{{'/js/page/report/backwater/backwaterItem.js?'.time()}}"></script>
@endsection
