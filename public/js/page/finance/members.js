$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    members(page);
});

function members(page){
    let searchForm = $('#searchForm');
    let url = '/manager/memberAssets?page='+page;
    $.ajax({
        url:url,data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}

function memberInfo(id){
    layerOpenIframe('【账号详情】','/manager/assetsInfo/?id='+id,['80%','80%']);
}

function balanceInfo(id){
    layerOpenIframe('【额度操作】','/manager/balanceInfo/?id='+id,['600px','500px']);
}

$(document).ready(function(){
    dataTables();
    $('#searchForm').keypress(function(e){
        if(e.which==13){
            members(1);
            return false;
        }
    });
});
