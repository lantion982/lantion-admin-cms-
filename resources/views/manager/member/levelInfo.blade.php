@include('manager.layouts.common')
<style>
    .form-group{margin-bottom:5px;}
    .form-control{display:inline;}
</style>
<div class="box box-info" style="padding:15px;">
    <div class="box-header with-border">
        <h3 class="box-title">会员等级</h3>
    </div>
    {{Form::model($level,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6">
                <label for="member_level_code">等级编号</label>
                @if(!empty($level))
                    <input id="id" name="id" value="{{$level->id}}" type="hidden">
                    @if(!empty($level->level_code))
                        {{Form::text('level_code',old('level_code'),['class'=>'form-control','ReadOnly'=>'true'])}}
                    @else
                        {{Form::select('level_code',config('enums.level_code'),null,['class'=>'form-control selectpicker',])}}
                    @endif
                @else
                    {{Form::select('level_code',config('enums.level_code'),null,['class'=>'form-control selectpicker',])}}
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <label for="company_id">等级名称</label>
                {{Form::text('level_name',old('level_name'),['class'=>'form-control'])}}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <label for="gift_money">赠送礼金</label>
                {{Form::number('gift_money',old('gift_money'),['class'=>'form-control','placeholder'=>'升级礼金'])}}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <label for="is_special">特殊等级</label>
                @if(empty($level))
                    <select class="form-control" name="is_special" id="is_special">
                        <option value="0">否</option>
                        <option value="1">是</option>
                    </select>
                @else
                    <select class="form-control" name="is_special" id="is_special">
                        <option value="0">否</option>
                        <option value="1" @if($level->is_special==1) selected @endif>是</option>
                    </select>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <label for="remarks">相关备注</label>
                {{Form::text('remarks',old('remarks'),['class'=>'form-control','placeholder'=>'相关备注'])}}
            </div>
        </div>
    </div>
    {{Form::close()}}
    <div class=" row box-footer pull-right">
        @if(empty($level))
            <button type="button" class="btn btn-info" id="btn-Add" onclick="createLevel()">新增等级</button>
        @else
            <button type="button" class="btn btn-info" id="btn-Update" onclick="updateLevel()">提交更新</button>
        @endif
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>
<script>
    function createLevel(){
        $.ajax({
            type:'post',
            url:'/manager/createMemberLevel',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getMemberLevel(1);
                    });
                }else{
                    layer.alert(data.msg);
                }
            },
            error:function(data){
                layer.alert('新增失败！',{icon:2,closeBtn:0});
            }
        });
    }

    function updateLevel(){
        $.ajax({
            type:'post',
            url:'/manager/updateMemberLevelInfo',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getMemberLevel(1);
                    });
                }else{
                    layer.alert(data.msg,function(){
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
