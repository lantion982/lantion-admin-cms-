$(document).ready(function() {
    $('#keyword').keypress(function (e) {
        if (e.which == 13) {
            getLogViewSMS(1);
            return false;
        }
    });
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    $('#CURRENT_PAGE').val(page);
    getLogViewSMS(page);
});

function getLogViewSMS(page){
    $('#btn-logViewLogin').button('loading');
    var searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/logPhoneSMS?page='+page,
        data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
		    dataTables();
        location.hash = page;
        $('#btn-logViewLogin').button('reset');
    })
}

function showEmptyLogViewSMS(){
  layerOpenIframe('新增手机号','/manager/showEmptyLogViewSMS','size.Normal')
}

function logViewSMSInfo(uid,phone){
    layerOpenIframe('【'+phone+'】详情','/manager/logViewSMSInfo/?log_phone_sms_id='+uid,'size.Normal');
}