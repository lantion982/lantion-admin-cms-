@include('manager.layouts.common')
<div class="box box-info" style="padding:20px;">
    <div class="box-header with-border">
        <h3 class="box-title">【{{$members->login_name}}】额度操作</h3>
    </div>
    <div class="box-body">
        <div class="col-md-6 col-sm-6">
            {{Form::model($members,['class'=>'form-bordered','id'=>'theform','name'=>'theform','method'=>'post'])}}
            {{Form::hidden('id',old('id'),['id'=>'id'])}}
            <div class="form-group mt1">
                {{Form::label('balance','余额：',['class'=>'control-label'])}}
                {{Form::label('balance',$members->balance,['class'=>'form-control-static'])}}
            </div>
            <div class="form-group mt1">
                {{Form::label('move_type','类型：',['class'=>'control-label'])}}
                {{Form::select('move_type',$moveType,old('move_type'),['class'=>'form-control'])}}
            </div>
            <div class="form-group mt1">
                {{Form::label('money','金额：',['class'=>'control-label'])}}
                {{Form::text('money',old('money'),['id'=>'money','class'=>'form-control','onkeyup'=>"this.value=this.value.replace(/[^\d.]/g,'');"])}}
            </div>
            <div class="form-group mt1">
                {{Form::label('commit','备注：',['class'=>'control-label'])}}
                {{Form::text('commit',old('commit'),['id'=>'recomnt','class'=>'form-control',])}}
            </div>
            <div class="form-group mt1">
                <button type="button" id="btn-update" class="btn btn-info" onclick="updateInfo('{{$members->id}}')">确认提交</button>
                <button type="button" class="btn btn-danger" onclick="layerCloseMe();">关闭本页</button>
            </div>
            {{Form::close()}}
        </div>
    </div>
</div>

<script>
    function updateInfo(){
        if($('#money').val()==''){
            layer.alert('请输入要变动的金额！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#recomnt').val()==''){
            layer.alert('请在备注中输入操作原因！',{icon:2,closeBtn:0});
            return false;
        }
        if(confirm('金额变动确认？请不要重复点击！')){
            $('#btn-updateMemberMoney').attr('disabled',true);
            $.ajax({
                type:'post',url:'/manager/updateMemberMoney',data:$('#theform').serialize(),dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                            layerCloseMe();
                            window.parent.members(1);
                        });
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,timeout:2000});
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试!',{icon:2,closeBtn:0,timeout:2000});
                }
            });
            return true;
        }
        return false;
    }
</script>
