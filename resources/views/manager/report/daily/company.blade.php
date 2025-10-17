@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">汇总报表</h5>
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
		                @include('manager.report.daily.menu')
	                    @include('manager.search')
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
	<div id="ajaxContent">
		@include('manager.report.daily.companyAjax')
	</div>
    <script type="text/javascript" src="{{'/js/page/report/daily/company.js'}}"></script>
@endsection
