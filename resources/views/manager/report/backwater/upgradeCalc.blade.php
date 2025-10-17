@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">会员升级结算条目</h5>
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
		                @include('manager.search')
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
	
    <div class="panel panel-default">
        <div id="ajaxContent"  class="box-body table-responsive no-padding">
            @include('manager.report.backwater.upgradeCalcList')
        </div>
    </div>
    <script type="text/javascript" src="{{'/js/page/report/backwater/upgradeCalc.js?'.time()}}"></script>
@endsection
