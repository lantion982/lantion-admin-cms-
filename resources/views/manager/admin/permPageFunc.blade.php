@extends('manager.superUI')
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <h4 class="subtitle mb2">权限:【{{$parentPermission->title}}】页面权限</h4>
        </div>
        <div class="col-lg-8">
            <div class="pull-right">
                <a href="javascript:" onclick="createPermission('{{$parentPermission->id}}')"
                   class="btn btn-info">
                    <i class="glyphicon glyphicon-plus"></i>&nbsp;新增权限
                </a>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="box-body">
            @include('manager.search')
        </div>
        <div id="ajaxContent"  class="box-body table-responsive no-padding">
            @include('manager.admin.permPageFuncList')
        </div>
    </div>
    <script type="text/javascript" src="{{'/js/page/admin/entrustPermPageFunc.js?'.time()}}"></script>
@endsection
