$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    getLoginLogs(page);
});

function getLoginLogs(page){
    var searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/logLogin/?page='+page,
        data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}

function getLogLogin(page){
    let types = $('#userType').val();
    let url = '';
    if(types=='App\\Models\\Member'){
        url = '/manager/logLoginMember?page='+page;
    }else if(types=='App\\Models\\Admin'){
        url = '/manager/logLoginAdmin?page='+page;
    }else{
        return;
    }
    searchForm = $('#searchForm');
    $.ajax({url:url,data:searchForm.serialize()}).done(function(data){
        $('#ajaxContent').html(data);
        dataTables();
        location.hash = page;
    });
}

$(document).ready(function(){
    dataTables();
    $('#keyword').keypress(function(e){
        if(e.which==13){
            getLogLogin(1);
            return false;
        }
    });
    
});
