@extends('manager.superUI')
@section('content')
<div class="row">
    <div class="panel panel-default" xmlns="http://www.w3.org/1999/html">
        <div class="box-header with-border">
            <h3 class="box-title">
                角色:【{{$role->title}}】的权限 </h3>
        </div>
        <div class="box-body">
            <form role="form" id="rolePermissionfrm" name="rolePermissionfrm" action="/" method="post">
                <input type="hidden" id="initNodes" name="initNodes" value="{{$listZtree}}">
                <input type="hidden" id="checkNodes" name="checkNodes" value="">
                <input type="hidden" id="id" name="id" value="@if(empty($role->id))@else{{$role->id}}@endif">
                <ul id="treeDemo" class="ztree"></ul>
                <div class="col-md-12">
                    <div class="box-footer">
                        <div class="pull-right">
                            <button type="button" class="btn btn-info" id="btn-update" onclick="updatePermission();">提交更新</button>
                            <button type="button" class="btn btn-danger" onclick="location.reload();">重置表单</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="{{'/plus/jQuery-ztree/bootstrapStyle/bootstrapStyle.css'}}" rel="stylesheet">
<script type="text/javascript" src="{{'/plus/jQuery-ztree/jquery.ztree.core.min.js'}}"></script>
<script type="text/javascript" src="{{'/plus/jQuery-ztree/jquery.ztree.excheck.min.js'}}"></script>
<SCRIPT type="text/javascript">
    function updatePermission(){
        $('#btn-update').button('loading');
        $.ajax({
            type:'POST',
            url:'/manager/updateRolePermission',
            data:$('#rolePermissionfrm :input[name!=\'initNodes\']').serialize(),
            dataType:'json',
            headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
            success:function(data){
                layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                    let index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                });
            },
            error:function(data){
                layer.alert('更新失败！',{icon:2,closeBtn:0});
            }
        });
    }

    let setting = {
        view:{
            addHoverDom:addHoverDom,
            removeHoverDom:removeHoverDom,
            selectedMulti:false
        },
        check:{
            enable:true
        },
        data:{
            simpleData:{
                enable:true
            }
        },
        callback:{
            onCheck:function(event,treeId,treeNode){
                let treeObj = $.fn.zTree.getZTreeObj(treeId);
                let nodes = treeObj.getCheckedNodes(true);
                if(nodes.length>0){
                    let selectNodesName = [];
                    let selectNodesId = [];
                    let setting = treeObj.setting.data.simpleData;
                    let id = setting.idKey;
                    nodes.forEach(function(el,index,arr){
                        selectNodesName.push(el['name']);
                        selectNodesId.push(el[id]);
                    });
                    $('#checkNodes').val(selectNodesId);
                }else{
                    $('#checkNodes').val('');
                }
            }
        }
    };

    let zNodes = eval($('#initNodes').val());
    $(document).ready(function(){
        let treeObj = $.fn.zTree.init($('#treeDemo'),setting,zNodes);
        treeObj.expandAll(true);
    });
    let newCount = 1;

    function addHoverDom(treeId,treeNode){
        let sObj = $('#'+treeNode.tId+'_span');
        if(treeNode.editNameFlag||$('#addBtn_'+treeNode.tId).length>0){
            return;
        }
        let addStr = '<span class=\'button add\' id=\'addBtn_'+treeNode.tId+'\' title=\'add node\' onfocus=\'this.blur();\'></span>';
        sObj.after(addStr);
        let btn = $('#addBtn_'+treeNode.tId);
        if(btn){
            btn.bind('click',function(){
                let zTree = $.fn.zTree.getZTreeObj('treeDemo');
                zTree.addNodes(treeNode,{id:(100+newCount),pId:treeNode.id,name:'new node'+(newCount++)});
                return false;
            });
        }
    }

    function removeHoverDom(treeId,treeNode){
        $('#addBtn_'+treeNode.tId).unbind().remove();
    }
</SCRIPT>@endsection
