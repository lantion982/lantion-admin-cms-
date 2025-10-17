@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">游戏类型-日报表明细</h5>
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
		                @include('manager.report.daily.menu',['exmenu'=>'游戏类型日报表-明细'])
		                @include('manager.search')
	                </div>
	            </div>
	        </div>
	    </div>
	</div>

	<div id="ajaxContent">
		@include('manager.report.daily.gTypeDetailAjax')
	</div>
    <script type="text/javascript" src="{{'/js/page/report/daily/gTypeDetail.js?'.time()}}"></script>
@endsection
