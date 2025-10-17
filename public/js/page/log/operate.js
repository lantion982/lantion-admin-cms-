$(document).ready(function() {
    $('#keyword').keypress(function (e) {
        if (e.which == 13) {
            getLogOperate(1);
            return false;
        }
    });
    dataTables();
});

$(document).on('click','.pagination a',function(e){
    e.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    getLogOperate(page);
});

function getLogOperate(page){
    $('#btn-logViewOperate').button('loading');
    var searchForm = $('#searchForm');
    $.ajax({
        url:'/manager/logOperation?page='+page,
        data:searchForm.serialize()
    }).done(function(data){
        $('#ajaxContent').html(data);
		    dataTables();
        location.hash = page;
        $('#btn-logViewOperate').button('reset');
    })
}
