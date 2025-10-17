function addMemberAccount(){
    layerOpenIframe('【添加帐号信息】','/manager/addMemberAccount',['780px','90%']);
}

function memberAccountInfo(id){
    layerOpenIframe('【帐号详情】','/manager/memberInfo?id='+id,'size.Ratio');
}

function getMemberIPLog(id){
    layerOpenIframe('登录日志','/manager/getMemberLoginLog/?id='+id);
}

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    getMAccount(page);
});

function getMAccount(page = 1){
    let searchForm = $('#searchForm');
    $.ajax({url:'/manager/memberAccount?page='+page,data:searchForm.serialize()}).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}

$(document).ready(function(){
    dataTables();
    $('#value1').keypress(function(e){
        if(e.which==13){
            getMAccount(1);
        }
    });
});
