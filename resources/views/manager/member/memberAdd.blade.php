@include('manager.layouts.common')
<style>
    .form-control{display:inline;}
</style>
<div class="box box-info" style="padding:15px">
    {{Form::model(null,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
    <div class="row">
        <div class="col-sm-6">
            <label>登录账号：</label>
            {{Form::text('login_name',old('login_name'),['class'=>'form-control'])}}
        </div>
        <div class="col-sm-6">
            <label>登录密码：</label>
            {{Form::password('login_pwd',['class'=>'form-control'])}}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <label>会员昵称：</label>
            {{Form::text('nick_name',old('nick_name'),['class'=>'form-control'])}}
        </div>
        <div class="col-sm-6">
            <label>手机号码：</label>
            {{Form::text('phone',old('phone'),['class'=>'form-control'])}}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div claass="form-group">
                <label>帐号状态：</label>
                {{ Form::radio('allow_login',1,1,['class'=>'mt1']) }}正常&nbsp;&nbsp;&nbsp;
                {{ Form::radio('allow_login',0,old('allow_login'),['class'=>'mt1']) }}禁止
            </div>
        </div>
        <div class="col-sm-6">
            <div claass="form-group">
                <label>性别：</label>
                {{ Form::radio('sex',1,1,['class'=>'mt1'])}}男&nbsp;&nbsp;&nbsp;
                {{ Form::radio('sex',0,old('sex'),['class'=>'mt1']) }}女
            </div>
        </div>
    </div>
    {{Form::close()}}
    <div class="box-footer  pull-right">
        <button type="button" class="btn btn-info" id="btn-Add" onclick="addMemberinfo()">新增会员</button>
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>
<script>
    function addMemberinfo(){
        $.ajax({
            type:'post',
            url:'/manager/createMemberAccount',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1},function(){
                        layerCloseMe();
                        window.parent.getMAccount(1);
                    });
                }else{
                    layer.alert(data.msg,{icon:2});
                }
            },
            error:function(data){
                layer.alert('新增失败！',{icon:5,closeBtn:0});
            }
        });
    }

</script>
