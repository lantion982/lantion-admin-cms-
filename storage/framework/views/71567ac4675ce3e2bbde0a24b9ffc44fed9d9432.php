<?php echo $__env->make('UEditor::head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<link href="<?php echo e('/css/fileinput.min.css'); ?>" rel="stylesheet" />
<script type="text/javascript" src="<?php echo e('/js/fileinput.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo e('/js/zh.js'); ?>"></script>
<div class="box box-info" style="padding:15px;">
    <div class="box-body">
        <?php echo e(Form::model($news,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm' ])); ?>

        <?php echo e(Form::hidden('id',old('id'))); ?>

        <div class="row mt1">
            <div class="form-group">
                <?php echo e(Form::label('title','新闻标题：',['class'=>''])); ?>

                <?php echo e(Form::text('title',old('title'),['class'=>'form-control','style'=>'width: 360px;'])); ?>

            </div>
        </div>
		<div class="row mt1">
			<div class="form-group">
				<?php echo e(Form::label('outer_link','外部链接：',['class'=>''])); ?>

				<?php echo e(Form::text('outer_link',old('outer_link'),['class'=>'form-control','style'=>'width: 360px;'])); ?>

			</div>
		</div>
	    <div class="row mt1">
		    <div class="form-group">
			    <?php echo e(Form::label('sorts','排序序号：',['class'=>''])); ?>

			    <?php echo e(Form::text('sorts',old('sorts'),['class'=>'form-control','style'=>'width: 360px;'])); ?>

		    </div>
	    </div>
        <div class="row">
            <div class="form-group">
                <?php echo e(Form::label('pic','电脑图片：',['class'=>'col-sm-1','style'=>'width:75px;padding:0px;'])); ?>

                <div class="col-sm-8" style="padding:0;">
                    <input id="uploadPic" type="file" name="image" class="file-loading" />
                </div>
                <?php echo e(Form::hidden('pic',old('pic'))); ?>

            </div>
        </div>
        <div class="row mt1">
            <div class="form-group">
                <?php echo e(Form::label('is_show','是否发布：',['class'=>''])); ?>

                <?php echo e(Form::radio('is_show',0,old('is_show')==0,['class'=>'minimal'])); ?>不发布&nbsp;&nbsp;
                <?php echo e(Form::radio('is_show',1,old('is_show')==1,['class'=>'minimal'])); ?>发布
                <?php echo e(Form::radio('is_show',2,old('is_show')==2,['class'=>'minimal'])); ?>已结束
            </div>
        </div>
        <div class="row mt1">
            <div class="form-group">
                <?php echo e(Form::label('begin_time','开始时间：',['class'=>'col-sm-1','style'=>'padding:0;width:75px'])); ?>

                <div class="col-sm-5" style="padding:0!important;width: 280px;">
                    <?php echo e(Form::text('begin_time',old('begin_time'),['class'=>'form-control query_time', 'style'=>'width: 276px;','readonly'=>"readonly"])); ?>

                </div>
                <?php echo e(Form::label('end_time','结束时间：',['class'=>'col-sm-1','style'=>'width:100px;'])); ?>

                <div class="col-sm-5" style="padding:0!important;width: 280px;">
                    <?php echo e(Form::text('end_time',old('end_time'),['class'=>'form-control query_time','style'=>'width: 276px;','readonly'=>"readonly"])); ?>

                </div>
            </div>
        </div>
        <?php echo e(Form::hidden('html',empty($news->content)?'':$news->content,['id'=>'html'])); ?>

        <div class="row mt2">
            <div class="form-group pr1">
                <script id="content" name="content" type="text/plain"></script>
            </div>
        </div>
        <?php echo e(Form::close()); ?>

    </div>
    <div class="box-footer  pull-right">
        <?php if(empty($news)): ?>
            <button type="button" class="btn btn-info" id="btn-Add" onclick="createNews();">新增新闻公告</button>
        <?php else: ?>
            <button type="button" class="btn btn-info" id="btn-Update" onclick="updateNewsInfo();">提交保存</button>
        <?php endif; ?>
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>

<script>
	$(document).ready(function(){
		lay('.query_time').each(function(){
			laydate.render({
				elem:this,type:'datetime',trigger:'click',position:'fixed',
			});
		});

		let ue = UE.getEditor('news_content',{
			toolbars:[
				['fullscreen','source','preview','undo','redo'],
				['fontsize','fontfamily','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat',
					'|','formatmatch','autotypeset','blockquote','pasteplain',
					'|','forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','cleardoc',
					'|','justifyleft','justifycenter','justifyright','justifyjustify',
					'|','horizontal','date','time','spechars','insertimage','link',
					'|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol',
					'mergecells','mergeright','mergedown','splittocells','splittorows','splittocols','charts','|']
			],
			autoHeightEnabled:true,
			autoFloatEnabled:true
		});
		ue.ready(function(){
			ue.execCommand('serverparam','_token','<?php echo e(csrf_token()); ?>');	//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
			ue.setContent($('#html').val());
		});
	});

	function createNews(){
		if($('#title').val()==''){
			layer.alert('请输入资讯标题！',{icon:2,closeBtn:0,offset:['50px']});
			return false;
		}
		/*if($('#show_type').val()==null){
			layer.alert('请选择新闻展现类型！',{icon:2,closeBtn:0,offset:['50px']});
			return false;
		}*/
		$.ajax({
			type:'post',
			url:'<?php echo e(route("newsCreate")); ?>',
			data:$('#postForm').serialize(),
			dataType:'json',
			success:function(data){
				if(data.status==0){
					layer.alert(data.msg,{icon:1,closeBtn:0,offset:['50px']},function(){
						layerCloseMe();
						window.parent.getNews(1);
					});
				}else{
					layer.alert(data.msg,{icon:2,closeBtn:0,offset:['50px']});
					return false;
				}
			},
			error:function(data){
				layer.alert('网络连接失败，请稍后重试！',{icon:2,closeBtn:0,offset:['50px']});
			}
		});
	}

	function updateNewsInfo(){
		if($('#title').val()==''){
			layer.alert('请输入资讯标题！',{icon:2,closeBtn:0,offset:['50px']});
			return false;
		}
		$.ajax({
			type:'post',
			url:'/manager/newsUpdate',
			data:$('#postForm').serialize(),
			dataType:'json',
			success:function(data){
				if(data.status==0){
					layer.alert(data.msg,{icon:1,closeBtn:0,offset:['50px']},function(){
						layerCloseMe();
						window.parent.getNews(1);
					});
				}else{
					layer.alert(data.msg,{icon:2,closeBtn:0,offset:['50px']});
				}
			},
			error:function(data){
				layer.alert('网络连接失败，请稍后重试！',{icon:5,closeBtn:0,offset:['50px']});
			}
		});
	}

	let initialPreview = "<?php echo e($pic_url); ?>";
	initialPreview = initialPreview===''?[]:[initialPreview];
	$("#uploadPic").fileinput({
		language: 'zh',//设置语言
		uploadUrl:"/manager/uploadDiscountPic",//上传的地址
		deleteUrl:'/manager/deleteDiscountPic',//删除地址
		allowedFileExtensions: ['jpg','jpeg','gif','png'],//接收的文件后缀
		uploadExtraData:{"filePath":'activity',_token:$('meta[name="csrf-token"]').attr('content')},
		uploadAsync: true,//默认异步上传
		showUpload:true,  //是否显示上传按钮
		showRemove :false,//显示移除按钮
		showPreview :true,//是否显示预览
		showCaption:false,//是否显示标题
		browseClass:"btn btn-primary",//按钮样式
		dropZoneEnabled: false,//是否显示拖拽区域
		//minImageWidth: 50,//图片的最小宽度
		//minImageHeight: 50,//图片的最小高度
		//maxImageWidth: 1000,//图片的最大宽度
		//maxImageHeight: 1000,//图片的最大高度
		maxFileSize:1024,//单位为kb，如果为0表示不限制文件大小
		minFileCount: 0,
		maxFileCount:1,//表示允许同时上传的最大文件个数
		initialPreview:initialPreview,
		initialPreviewConfig:JSON.parse('<?php echo json_encode($picConfig); ?>'),
		validateInitialCount:true,
		overwriteInitial: false,
		//initialPreviewShowDelete:false,
		append:false,
		msgFilesTooMany: "选择上传的文件数量({n}) 超过允许的最大数值{m}！",
		initialPreviewAsData: true,//是否仅发送预览数据，而不发送原始标记
		deleteExtraData:{_token:$('meta[name="csrf-token"]').attr('content')}
	}).on("fileuploaded",function (event,data,previewId,index) {
		let new_pic = data.response.initialPreviewConfig[0].key;
		$('input[name="pic"]').val(new_pic);

	}).on('filepredelete',function(event,key) {
		$('input[name="pic"]').val('');
	});
</script>
<?php /**PATH D:\www\ganglan\resources\views/manager/news/info.blade.php ENDPATH**/ ?>