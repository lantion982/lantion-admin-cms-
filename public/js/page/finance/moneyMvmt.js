$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    moneyMvmt(page);
});

function moneyMvmt(page){
    let searchForm = $('#searchForm');
    let url = '/manager/moneyMovement?page='+page;
    $.ajax({
        url:url,data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        dataTables();
    });
}

$(document).ready(function(){
    $('#searchForm').keypress(function(e){
        if(e.which==13){
            moneyMvmt(1);
            return false;
        }
    });
    dataTables();
});







