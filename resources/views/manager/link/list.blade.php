@extends('manager.superUI')
@section('content')
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">网址导航</h5>
	                <div class="box-tools pull-right">
		                <button type="button" title="添加网址信息" class="btn btn-default btn-sm" onclick="addLink()">
	                        <i class="fa fa-plus-square"></i>&nbsp;添加网址信息
	                    </button>
		                <button type="button" title="刷新" class="btn btn-box-tool" onclick="location.reload();">
                            <i class="fa fa-refresh"></i></button>
	                    <button type="button" title="展开&收缩" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
	                </div>
	            </div>
                <div class="box-body">
                    <div class="row">
                        @include('manager.search')
                    </div>
                </div>
	        </div>
	    </div>
	</div>
    <div class="panel panel-default">
        <div id="ajaxContent"  class="box-body table-responsive no-padding">
            @include('manager.link.listAjax')
        </div>
    </div>
    <script type="text/javascript" src="{{'/js/page/link/link.js?'.time()}}"></script>
@endsection
