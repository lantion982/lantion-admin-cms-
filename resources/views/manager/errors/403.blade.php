@extends('manager.layouts.blank')
@section('content')
	<div class="error-page">
		<h2 class="headline text-yellow"> 403</h2>
		<div class="error-content">
			<h3><i class="fa fa-warning text-yellow"></i> 操作错误</h3>
			<p>对不起，你没有权限操作这个页面！</p>
			<p>如果需要访问这个页面，请与系统管理员联系。</p>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<a href="/manager" class="btn btn-warning btn-block">跳转首页</a>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<a href="{{ $previousUrl }}" class="btn btn-default btn-block">点击返回</a>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection