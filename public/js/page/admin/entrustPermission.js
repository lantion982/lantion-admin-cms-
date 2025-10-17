function binding(){
    tableCheckAll('tbl-activities');
    banding_showSub();
}

function banding_showSub(){
    $(".show-sub-permissions").click(function () {
        let id = $(this).data('id'), subSelector = $('.parent-permission-' + id);
        if (subSelector.hasClass("hide")) {
            $(this).children('.glyphicon').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
            subSelector.removeClass('hide');
        } else {
            $(this).children('.glyphicon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
            subSelector.addClass('hide');
        }
    });
}

function delPermission(id){
    let index = layer.open({
        title:'操作提示',content:'确认要删除吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/deletePermission',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({title:'操作提示',content:data.msg,icon:1,btn:'确定',closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    getPermissions(1,0);
                },
                error:function(data){
                    let index2 = layer.open({title:'操作提示',content:'网络连接失败，请刷新后重试！',btn:'确定',icon:2,closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                }
            });
        },
        btn2: function(){
            getPermissions(1,0);
        }
    });
}

function addPermission(){
    layerOpenIframe('【权限详情】','/manager/addPermission','size.Normal')
}

function permissionInfo(id){
    layerOpenIframe('【权限详情】','/manager/permissionInfo?id='+id,'size.Normal')
}

function createSubPermission(id) {
    layerOpenIframe('【权限详情】','/manager/addSubPermission?id='+id,'size.Normal')

}

function permPageFunc(id){
    layerOpenIframe('权限配置',['/manager/permPageFunc?id='+id,'no'],['96%','96%'])
}

$(document).ready(function() {
    binding();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    getPermissions(page,0);
});

function getPermissions(page,id){
    $.ajax({
        url:'/manager/permission/?page='+page
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        ajaxBinding();
        if(id != ''){
            expand(id);
        }
    });
}

//子权限点击展开事件
function expand(id){
    let selfSelector = $('.parent-permission-' + id);
    if(selfSelector){
    
    }
    let subSelectors = selfSelector.prevAll();
    for (let i=0;i<subSelectors.length;i++){
    
    }
}
