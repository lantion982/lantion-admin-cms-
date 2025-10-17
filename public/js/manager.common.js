//放在公共页面的自定义js
function AdminLteStyle(){
    $('input[type="checkbox"].switch').bootstrapSwitch('size', 'mini');
}

function myaddTabs(id,title,url){
    let option={
        id:id,
        title:title,
        close:true,
        url:url
    };
    parent.addTabs(option);
}

//页面的check 支持 checkAll 方法
function tableCheckAll(tableId){
    let localTableId = '#'+tableId;
    let table = $(localTableId);
    let checkAll = table.find('input.all');
    let checkboxes =table.find('input.check');

    checkAll.on('ifChecked ifUnchecked', function(event) {
        if (event.type == 'ifChecked') {
            checkboxes.iCheck('check');
        } else {
            checkboxes.iCheck('uncheck');
        }
    });

    checkboxes.on('ifChanged', function(event){
        if(checkboxes.filter(':checked').length == checkboxes.length) {
            checkAll.prop('checked', true);
        } else {
            checkAll.prop('checked', false);
        }
        checkAll.iCheck('update');
    });
}

//checkbox 是否有被选定的选定项
function atLeastOneIsChecked(tableId) {
    let localTableId = '#' + tableId;
    let table = $(localTableId);
    let atLeastOneIsChecked = table.find('input.check:checked').length > 0;
    return (atLeastOneIsChecked >0);
}

function getTableCheckedValues(tableId){
    let localTableId = '#' + tableId;
    let table = $(localTableId);
    let checked_array = table.find('input.check:checked');
    let id_array = new Array();
    checked_array.each(function(){
        id_array.push($(this).val());
    });
    return id_array.join(',');
}

// 实现页面的 sortNumber 方法，ajax提交更新页面的 sort
function tableBtnUpDownMove(tableId){
    let localTableId = '#'+tableId;
    let table = $(localTableId);

    table.find("a[name='upMove']").bind("click",function(){
        let $this  = $(this);
        let curTr = $this.parents("tr");
        let prevTr = $this.parents("tr").prev();
        if(prevTr.length == 0){
            return false;
        }else{
            prevTr.before(curTr);
            sortNumber(tableId);
        }
    });

    table.find("a[name='downMove']").bind("click",function(){

        let $this  = $(this);
        let curTr = $this.parents("tr");
        let nextTr = $this.parents("tr").next();
        if(nextTr.length == 0){
            return false;
        }else{
            nextTr.after(curTr);
            sortNumber(tableId);
        }
    });
    //排序
    table.find("input[name='orderNum']").bind("change",function(){
        let $this = $(this);
        //获得当前行
        let curTr = $this.parents("tr");
        let curOrderNum = $this.val();
        //当前行同级的所有行
        let siblingsTrs = curTr.siblings();
        if(siblingsTrs.length >0){
            for(let i in siblingsTrs){
                let otherOrderNum = $(siblingsTrs[i]).children().find("input[name='orderNum']").val();
                if(parseInt(curOrderNum) <= parseInt(otherOrderNum)){
                    $(siblingsTrs[i]).before(curTr);
                    sortNumber(tableId);
                    break;
                }
            }
        }
    });
}

function layerOpenIframe(title,url,size='size.Normal',offset='30px'){
    let vive_width = document.body.clientWidth;
    let area = ['90%','90%'];
    if(size == 'size.Normal'){
        if(vive_width>1200){
            area = ['960px','90%'];
        }
    }else if(size == 'size.Large'){
        area = ['96%','90%'];
    }else if(size == 'size.Ratio'){
        offset = '30px';
        area = ['90%','90%'];
    }else{
        offset = '30px';
        area = size;
    }
    layer.open({
        type: 2,
        offset: offset,
        title: title,
		fix: false,
		maxmin: true,
        skin: 'layui-layer-rim',
        shadeClose: true,
        area: area,
        closeBtn: 1,
        content:url
    });
}

function layerCloseMe(){
    let index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}

function dataTables() {
    $('#tbl-activities').DataTable({
        "paging":   false,
        "bFilter" : false,
        "bInfo":false,
        'language': {
            'emptyTable': '未能查询到符合要求的信息!',
            'loadingRecords': '加载中...',
            'processing': '查询中...',
            'search': '检索:',
            'lengthMenu': '每页 _MENU_ 条记录',
            'zeroRecords': '没有数据',
            'paginate': {
                'first':      '第一页',
                'last':       '最后页',
                'next':       '下一页',
                'previous':   '上一页'
            },
            'infoEmpty': '没有数据',
            'infoFiltered': '(过滤总件数 _MAX_ 条)'
        }
    });
}
