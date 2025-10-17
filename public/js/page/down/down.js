function binding(){
    dataTables();
    switchDown();
}

function delDown(id){
    let index = layer.open({
        icon:3,title:'操作提示',content:'确认要删除吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/delDown',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        winindex = layer.alert(data.msg,{icon:1,closeBtn:0,time:1000},function(){
                            layer.close(winindex);
                        });
                        getLink(1);
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
                        return false;
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,time:2000});
                }
            });
        },
        btn2:function(){
        }
    });
}

function addDown(){
    layerOpenIframe('【添加网址信息】','/manager/addDown');
}

function editDown(id){
    layerOpenIframe('【网址信息详情】','/manager/editDown/?id='+id,);
}

function switchDown(){
    $('input[type="checkbox"].switch').on('switchChange.bootstrapSwitch',function(event,state){
        id = this.value;
        is_show = '1';
        if(state){
            is_show = '1';
        }else{
            is_show = '0';
        }
        updateDown(id,is_show);
    });
}

function updateDown(id,is_show){
    let index = layer.open({
        icon:3,title:'操作提示',content:'确定更改信息状态吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',
                url:'/manager/updateDown',
                data:{id:id,is_show:is_show},
                dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        winindex = layer.alert(data.msg,{icon:1,closeBtn:0,time:1000},function(){
                            layer.close(winindex);
                        });
                        get_news(parseInt($('#page').val()));
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
                        return false;
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,time:2000});
                }
            });
        },
        btn2:function(){
        }
    });
}

$(document).ready(function(){
    binding();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    getDown(page);
});

function getDown(page = 1){
    let searchForm = $('#searchForm');
    $.ajax({url:'/manager/downList?page='+page,data:searchForm.serialize()}).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        binding();
    });
}
