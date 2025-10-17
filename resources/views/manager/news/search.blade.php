<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-2 col-sm-2">
			<input type="text" id="keyword" name="keyword" placeholder="新闻标题" class="form-control">
		</div>
		<div class="col-md-2 col-sm-2 hidden-sm hidden-xs">
			{{Form::select('show_type',$newsType,null,['id'=>'show_type','class'=>'form-control selectpicker',])}}
		</div>

		<div class="col-md-2 col-sm-2">
			<button type="button" class="form-control btn btn-info" onclick="get_news('1')" id="submitSearch">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-2 col-sm-2">
			<button type="button" class="form-control btn btn-danger" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
	@include('manager.searchScript')
</form>
