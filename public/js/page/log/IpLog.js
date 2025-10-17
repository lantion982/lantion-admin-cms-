$(document).ready(function(){
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    getIpLog(page);
});

function getIpLog(page){
    $.ajax({url:'/manager/ipRegisterLogin/?page='+page}).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}

function ipReset(id){
    let index = layer.open({icon:3,title:'操作提示',content:'确定要重置该IP吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/ipReset',data:{id:id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        winindex = layer.alert(data.msg,{icon:1,closeBtn:0,time:1000},function(){
                            layer.close(winindex);
                        });
                        getIpLog(1);
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,time:2000});
                }
            });
        },
        btn2:function(){}
    });
}

function addIpBlack(id,blackId){
    let msg = '';
    if(blackId===''){
        msg = '确定要把该IP设置为黑名单吗？';
    }else{
        msg = '确定要移除黑名单吗？';
    }
    let index = layer.open({icon:3,title:'操作提示',content:msg,btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/addIpBlack',data:{id:id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        winindex = layer.alert(data.msg,{icon:1,closeBtn:0,time:1000},function(){
                            layer.close(winindex);
                        });
                        getIpLog(1);
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,time:2000});
                }
            });
        },
        btn2:function(){}
    });
}
