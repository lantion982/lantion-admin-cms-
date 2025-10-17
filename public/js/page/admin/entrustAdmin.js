function ajaxBinding(){
    AdminLteStyle();
    binding();
}

function binding(){
    dataTables();
    binging_switch();
}

function delAdmin(adminId){
    let index = layer.open({
        title:'操作提示',
        content:'确认要删除么？删除后不能恢复！',
        btn:['确定','取消'],
        closeBtn:0,
        yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/delAdminInfo',data:{'admin_id':adminId},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        layer.alert(data.msg,{icon:1,title:'操作提示',closeBtn:0,time:1000},function(){
                            getAdmin(1);
                            layerCloseMe();
                        });
                    }else{
                        layer.alert(data.msg,{icon:2,time:2000});
                    }
                },
                error:function(data){
                    layer.alert('数据提交失败，请稍后重试!',{icon:2,time:2000,title:'操作提示'});
                }
            });
        },
        btn2:function(){}
    });
}

function createAdmin(){
    layerOpenIframe('【添加后台账号】','/manager/addAdminInfo',['68%','80%']);
}

function adminInfo(admin_id){
    layerOpenIframe('帐号详情','/manager/adminInfo/?admin_id='+admin_id,['68%','80%']);
}

function binging_switch(){
    $('input[type="checkbox"].switch').on('switchChange.bootstrapSwitch',function(event,state){
        admin_id = this.value;
        is_active = '1';
        if(state){
            is_active = '1';
        }else{
            is_active = '0';
        }
        updateAdminStatus(admin_id,is_active);
    });
}

function getAdmin(page){
    let searchForm = $('#searchForm');
    $.ajax({
        type:'get',url:'/manager/adminList?page='+page,data:searchForm.serialize(),
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success:function(data){
            $('#ajaxContent').html(data);
            location.hash = page;
            ajaxBinding();
        },
        error:function(){
            layer.alert('网络连接失败，请刷新后重试!',{icon:2});
        }
    });
}

function updateAdminStatus(admin_id,is_allow){
    $.ajax({
        type:'post',
        url:'/manager/updateAdminStatus',
        data:{admin_id:admin_id,is_allow:is_allow},
        dataType:'json',
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        },
        success:function(data){
            if(data.status==0){
                layer.alert(data.msg,{icon:1,closeBtn:0,time:1000});
                getAdmin(parseInt($('#page').val()));
            }else{
                layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
            }
        },
        error:function(data){
            layer.alert('网络连接失败，请刷新后重试!',{icon:2,closeBtn:0,time:2000});
        }
    });
}

$(document).ready(function(){
    binding();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    page = $(this).attr('href').split('page=')[1];
    $('#CURRENT_PAGE').val(page);
    getAdmin(page);
});
