function binding(){
    dataTables();
    bing_ipSwitch();
}

function ajaxBinding(){
    AdminLteStyle();
    binding();
}

function delIP(id){
    let index = layer.open({title:'操作提示',content:'确认要删除吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/delIP',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({title:'操作提示',icon:1,content:data.msg,btn:'确定',closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    getIP(1);
                },
                error:function(data){
                    let index2 = layer.open({title:'操作提示',icon:2,content:'网络连接失败，请刷新后重试！',btn:'确定',closeBtn:0,yes:function(){
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

function addIP(){
    layerOpenIframe('【添加IP黑白名单】','/manager/addIP');
}

function editIP(id){
    layerOpenIframe('详情','/manager/editIP/?id='+id);
}

function bing_ipSwitch(){
    $('input[type="checkbox"].switch').on('switchChange.bootstrapSwitch',function(event,state){
        id = this.value;
        is_active = '1';
        if(state){
            is_active = '1';
        }else{
            is_active = '0';
        }
        updateIP(id,is_active);
    });
}

function updateIP(id,is_active){
    let index = layer.open({icon:3,title:'操作提示',content:'确认要改变状态吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/updateIP',data:{id:id,is_active:is_active},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({title:'操作提示',icon:1,content:data.msg,btn:'确定',closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    
                },
                error:function(data){
                    let index2 = layer.open({title:'操作提示',icon:2,content:'网络连接失败，请刷新后重试！',btn:'确定',closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                }
            });
        },
        btn2:function(){
            getIP(parseInt($('#page').val()));
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
    getIP(page);
});

function getIP(page){
    let searchForm = $('#searchForm');
    $.ajax({
        type:'get',url:'/manager/listIP?page='+page,data:searchForm.serialize(),
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
