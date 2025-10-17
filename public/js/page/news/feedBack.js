$(document).ready(function(){
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    let page = $(this).attr('href').split('page=')[1];
    $('#page').val(page);
    getFeedBack(page);
});

function getFeedBack(page){
    $('#btn-feedBack').button('loading');
    let searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/feedBack?page='+page,
        data:searchForm.serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    }).done(function(data){
        $('#ajaxContent').html(data);
        location.hash = page;
        $('#btn-feedBack').button('reset');
        dataTables();
    });
}

function clearSearch(){
    $('#keyword').val('');
}
