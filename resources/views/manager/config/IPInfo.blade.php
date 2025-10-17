@include('manager.layouts.common')
<div class="box box-info" style="padding:15px;">
    <div class="box-header with-border">
        <h3 class="box-title">IP黑白名单详情</h3>
    </div>
    <div class="box-body">
        {{Form::model($ipInfo,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
        {{Form::hidden('id',old('id'))}}
        <div class="row mt1">
            <div class="col-sm-6">
                <label>IP 地址：</label>
                {{Form::text('ip_addr',old('ip_addr'),['class'=>'form-control'])}}
            </div>
            <div class="col-sm-6">
                <label>host地址：</label>
                {{Form::text('host_name',old('host_name'),['class'=>'form-control'])}}
            </div>
        </div>
        <div class="row mt1">
            <div class="col-sm-6">
                <label>针对平台：</label>
                {{Form::select('host_type',config('enums.host_type'),null,['class'=>'form-control'])}}
            </div>
            <div class="col-sm-6">
                <label>名单类型：</label>
                {{Form::select('block_type',config('enums.block_type'),null,['class'=>'form-control'])}}
            </div>
        </div>

        <div class="row mt1">
            <div class="col-sm-6">
                <label>相关描述：</label>
                {{Form::text('remarks',old('remarks'),['rows'=>'6','class'=>'form-control',])}}
            </div>
        </div>
        <div class="row mt1">
            <div class="col-sm-6">
                <label>是否生效：</label>
                {{Form::radio('is_active',0,old('is_active')=== 0,['class'=>'minimal'])}}否&nbsp;
                {{Form::radio('is_active',1,old('is_active')=== 1,['class'=>'minimal'])}}是
            </div>
        </div>
        {{Form::close()}}
    </div>
    <div class="box-footer  pull-right">
        @if(empty($ipInfo))
            <button type="button" class="btn btn-info" id="btn-Add" onclick="createIP()">新增黑白名单</button>
        @else
            <button type="button" class="btn btn-info" id="btn-Update" onclick="updateIPInfo()">提交更新</button>
        @endif
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>

<script>
    $(document).ready(function(){
        let ip_host_type = $('#host_type').val();
    });

    function createIP(){
        $.ajax({
            type:'post',
            url:'/manager/createIP',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getIP(1);
                    });
                }else{
                    layer.alert(data.msg,function(){
                        layerCloseMe();
                    });
                }
            },
            error:function(data){
                layer.alert('新增失败！',{icon:2,closeBtn:0});
            }
        });
    }

    function updateIPInfo(){
        $.ajax({
            type:'post',
            url:'/manager/updateIP',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getIP(parseInt($('#page',window.parent.document).val()));
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0},function(){
                        layerCloseMe();
                    });
                }
            },
            error:function(data){
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            }
        });
    }
</script>
