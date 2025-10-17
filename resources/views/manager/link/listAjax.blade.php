<div class="row" style="margin:0;">
    <div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0;">
        <ol class="breadcrumb mb1">
            @foreach($pclass as $val)
            <li>
                <a href="{{route('linkList')}}?pid={{$val->id}}">
                    {{$val->title}}
                </a>
            </li>
           @endforeach
        </ol>
    </div>
</div>
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="{{$page}}">
    <thead>
    <tr>
        <th>序</th>
        <th>标题</th>
        <th class="t-c hidden-sm hidden-xs">图标</th>
        <th class="hidden-sm hidden-xs">网址</th>
        <th class="t-c hidden-xs">排序</th>
        <th class="t-c hidden-xs">状态</th>
        <th class="t-c hidden-xs">推荐</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    @foreach($links as $key=>$link)
        <tr>
            <td>{{++$key+(20*($page-1))}}</td>
            <td>{{$link->title}}</td>
            <td class="t-c hidden-sm hidden-xs"><img src="{{$link->icon??''}}" height="25"></td>
            <td class="hidden-sm hidden-xs">{{$link->link}}</td>
            <td class="t-c hidden-xs">{{$link->sorts}}</td>
            <td class="t-c hidden-xs">
                @if($link->is_show==0)
                    <bottn class="btn btn-default btn-sm" onclick="updateLink('{{$link->id}}',1)" name="isshow">隐藏</bottn>
                @elseif($link->is_show==1)
                    <bottn class="btn btn-success btn-sm" onclick="updateLink('{{$link->id}}',0)" name="isshow">显示</bottn>
                @endif
            </td>
            <td class="t-c hidden-xs">
                @if($link->is_hot==0)
                    <bottn class="btn btn-default btn-sm" onclick="updateHot('{{$link->id}}',1)" name="ishot">否</bottn>
                @elseif($link->is_hot==1)
                    <bottn class="btn btn-success btn-sm" onclick="updateHot('{{$link->id}}',0)" name="ishot">是</bottn>
                @endif
            </td>
            <td class="t-c">
                <a href="javascript:" class="btn btn-success btn-sm" onclick="editLink('{{$link->id}}')">详情</a>&nbsp;
                <a href="javascript:" class="btn btn-danger btn-sm" onclick="delLink('{{$link->id}}')">删除</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="box-info">
   {{$links->links()}}
</div>
