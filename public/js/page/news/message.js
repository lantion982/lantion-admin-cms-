function delMessage(Id){
    $.ajax({
        type:'post',url:'/manager/messageDel',data:{'id':Id},dataType:'json',
        headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
        success:function(data){
            if(data.status==1){
                winindex = layer.alert(data.msg,{icon:1,closeBtn:0,time:1000},function(){
                    layer.close(winindex);
                });
                getMessage(parseInt($('#page').val()));
            }else{
                layer.alert(data.msg,{icon:2,closeBtn:0,time:2000});
                return false;
            }
        },
        error:function(data){
            layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,time:2000});
        }
    });
}

function messageAdd(){
    layerOpenIframe('【发送信息】','/manager/messageAdd',['800px','620px;']);
}

$(document).ready(function(){
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    getMessage(page);
});

function getMessage(page){
    let searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/messageList?page='+page,
        data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}
