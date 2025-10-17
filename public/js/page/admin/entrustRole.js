function ajaxBinding(){
    AdminLteStyle();
    binding();
}

function binding(){
    dataTables();
}

function delRole(id){
    let index = layer.open({
        title:'操作提示',content:'确认要删除吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/deleteRole',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({
                        title:'操作提示',content:data.msg,icon:1,btn:'确定',closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    getEntrustRole(1);
                },
                error:function(data){
                    let index2 = layer.open({
                        title:'操作提示',content:'网络连接失败，请刷新后重试！',btn:'确定',icon:2,closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    
                }
            });
        },
        btn2:function(){
        }
    });
}

function createRole(){
    layerOpenIframe('【添加角色】','/manager/addRole','size.Normal');
}

function roleInfo(id){
    layerOpenIframe('【角色详情】','/manager/roleInfo/?id='+id,'size.Normal');
}

function rolePermission(id){
    layerOpenIframe('【角色权限】','/manager/rolePermission/?id='+id,'size.Normal');
}

function getEntrustRole(page){
    let searchForm = $('#searchForm');
    $.ajax({
        type:'get',url:'/manager/role?page='+page,data:searchForm.serialize(),
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

$(document).ready(function(){
    binding();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    getEntrustRole(page);
});
