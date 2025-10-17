function binding(){
    banding_showSubPermissions();
}

function banding_showSubPermissions(){
    $('.show-sub-permissions').click(function(){
        let id = $(this).data('id'),subSelector = $('.parent-permission-'+id);
        if(subSelector.hasClass('hide')){
            $(this).children('.glyphicon').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
            subSelector.removeClass('hide');
        }else{
            $(this).children('.glyphicon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
            subSelector.addClass('hide');
        }
    });
}

function delPagePermission(id){
    let index = layer.open({title:'操作提示',content:'确认要删除吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/deletePermission',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({title:'操作提示',content:data.msg,btn:'确定',icon:1,closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    getPermissions($('#PERMISSION_ID').val(),1);
                },
                error:function(data){
                    let index2 = layer.open({
                        title:'操作提示',
                        content:'网络连接失败，请刷新后重试！',
                        btn:'确定',
                        closeBtn:0,
                        yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    
                }
            });
        },
    });
}

function permpage_Info(id){
    layerOpenIframe('【权限详情】','/manager/permissionInfo?id='+id,'size.Normal');
}

function createPermission(id){
    layerOpenIframe('【权限详情】','/manager/addSubPermission?id='+id,'size.Normal');
}

$(document).ready(function(){
    binding();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    getPermissions($('#PERMISSION_ID').val(),page);
});

function getPermissions(id,page){
    $.ajax({
        url:'/manager/permPageFunc/?id='+id+'&page='+page
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        ajaxBinding();
    });
}
