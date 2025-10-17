function delLevel(id){
    let index = layer.open({icon:3,title:'操作提示',content:'确认要删除该信息吗？',btn:['确定','取消'],closeBtn:0,yes:function(){
            layer.close(index);
            $.ajax({
                type:'post',url:'/manager/deleteMemberLevel',data:{'id':id},dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    let index2 = layer.open({title:'操作提示',content:data.msg,btn:'确定',icon:1,closeBtn:0,yes:function(){
                            layer.close(index2);
                            layer.closeAll('loading');
                        }
                    });
                    getMemberLevel(1);
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
        btn2:function(){
        }
    });
}

function createLevel(){
    layerOpenIframe('【添加会员等级】','/manager/addMemberLevel');
}

function levelInfo(id){
    layerOpenIframe('【会员等级详情】','/manager/memberLevelInfo/?id='+id);
}

$(document).ready(function(){
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    getMemberLevel(page);
});

function getMemberLevel(page){
    let searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/memberLevel/?page='+page,
        data:searchForm.serialize(),
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}






