$(function(){
    //加载弹出层
    /* layui.use(['form','element'],
	 function() {
		 layer = layui.layer;
		 element = layui.element;
	 });*/
    
    //触发事件
    var tab = {
        tabAdd:function(title,url,id){
            //新增一个Tab项
            element.tabAdd('xbs_tab',{
                title:title
                ,content:'<iframe tab-id="'+id+'" frameborder="0" src="'+url+'" scrolling="yes" class="x-iframe" id="mainfram"></iframe>'
                ,id:id
            });
        }
        ,tabDelete:function(id){
            //删除指定Tab项
            element.tabDelete('xbs_tab',id);
            //othis.addClass('layui-btn-disabled');
        }
        ,tabChange:function(id){
            //切换到指定Tab项
            element.tabChange('xbs_tab',id); //切换到：用户管理
        }
    };
    
    tableCheck = {
        init:function(){
            $('.layui-form-checkbox').click(function(event){
                if($(this).hasClass('layui-form-checked')){
                    $(this).removeClass('layui-form-checked');
                    if($(this).hasClass('header')){
                        $('.layui-form-checkbox').removeClass('layui-form-checked');
                    }
                }else{
                    $(this).addClass('layui-form-checked');
                    if($(this).hasClass('header')){
                        $('.layui-form-checkbox').addClass('layui-form-checked');
                    }
                }
                
            });
        },
        getData:function(){
            var obj = $('.layui-form-checked').not('.header');
            var arr = [];
            obj.each(function(index,el){
                arr.push(obj.eq(index).attr('data-id'));
            });
            return arr;
        }
    };
    
    //开启表格多选
    tableCheck.init();
    
    $('.container .left_open i').click(function(event){
        if($('.left-nav').css('left')=='0px'){
            $('.left-nav').animate({left:'-221px'},100);
            $('.page-content').animate({left:'0px'},100);
            $('.page-content-bg').hide();
        }else{
            $('.left-nav').animate({left:'0px'},100);
            $('.page-content').animate({left:'221px'},100);
            if($(window).width()<768){
                $('.page-content-bg').show();
            }
        }
        
    });
    
    $('.page-content-bg').click(function(event){
        $('.left-nav').animate({left:'-221px'},100);
        $('.page-content').animate({left:'0px'},100);
        $(this).hide();
    });
    
    $('.layui-tab-close').click(function(event){
        $('.layui-tab-title li').eq(0).find('i').remove();
    });
    
    $('.layui-nav-item to-index').click(function(event){
        var url = $(this).children('a').attr('data-href');
        //alert (url);
        var title = $(this).find('cite').html();
        tab.tabDelete(1);
        tab.tabAdd(title,url,1);
        tab.tabChange(1);
        event.stopPropagation();
    });
    //左侧菜单效果
    // $('#content').bind("click",function(event){
    $('.left-nav #nav li').click(function(event){
        if($(this).children('.sub-menu').length){
            if($(this).hasClass('open')){
                $(this).removeClass('open');
                $(this).find('.nav_right').html('&#xe697;');
                $(this).children('.sub-menu').stop().slideUp();
                $(this).siblings().children('.sub-menu').slideUp();
            }else{
                $(this).addClass('open');
                $(this).children('a').find('.nav_right').html('&#xe6a6;');
                $(this).children('.sub-menu').stop().slideDown();
                $(this).siblings().children('.sub-menu').stop().slideUp();
                $(this).siblings().find('.nav_right').html('&#xe697;');
                $(this).siblings().removeClass('open');
            }
        }else{
            
            var url = $(this).children('a').attr('data-href');
            var title = $(this).find('cite').html();
            var index = $('.left-nav #nav li').index($(this));
            
            for(var i = 0; i<$('.x-iframe').length; i++){
                if($('.x-iframe').eq(i).attr('tab-id')==index+1){
                    tab.tabChange(index+1);
                    event.stopPropagation();
                    return;
                }
            }
            
            tab.tabAdd(title,url,index+1);
            tab.tabChange(index+1);
        }
        
        event.stopPropagation();
        
    });
});

//弹出层*/
//参数：title-标题；url-请求的url；id:id;w=宽度；h-高度*/
function x_admin_show(title,url,w,h){
    if(title==null||title==''){
        title = false;
    }
    if(url==null||url==''){
        url = '404.html';
    }
    if(w==null||w==''){
        w = ($(window).width()*0.9);
    }
    if(h==null||h==''||h>$(window).height()){
        h = ($(window).height()-20);
    }
    layer.open({type:2,area:[w+'px',h+'px'],fix:false,maxmin:true,shadeClose:true,shade:0.4,title:title,content:url});
}

/*关闭弹出框口*/
function x_admin_close(){
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}

//只能输入数字和小数点
function numberonly(ob){
    if(!ob.value.match(/^[\+\-]?\d*?\.?\d*?$/)){
        ob.value = ob.t_value;
    }else{
        ob.t_value = ob.value;
    }
    if(ob.value.match(/^(?:[\+\-]?\d+(?:\.\d+)?)?$/)){
        ob.o_value = ob.value;
    }
}

//ajax请求
function ajaxUpadate(url,data = null,callBack,method = 'post'){
    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type:method,
        url:url,
        data:data,
        dataType:'json',
        success:function(data){
            callBack(data);
        },
        error:function(data){
            layer.alert('系统错误！',{icon:2})
        }
    });
}

