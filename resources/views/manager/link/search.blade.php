<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-2 col-sm-2">
			<input type="text" id="keyword" name="keyword" placeholder="关键字" class="form-control" value="{{$data['keyword']??''}}">
		</div>
		<div class="col-md-2 col-sm-2 hidden-sm hidden-xs">
            <select id="cid" name="cid" class="form-control selectpicker">
                <option value="0">全部分类</option>
                @foreach($linkClass as $val)
                   <option value="{{$val->id}}" @if($val->id==$data['cid']) selected @endif >{{$val->title}}</option>
                @endforeach
            </select>
		</div>

		<div class="col-md-2 col-sm-2">
			<button type="button" class="form-control btn btn-info" onclick="getLink('1')" id="submitSearch">
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
