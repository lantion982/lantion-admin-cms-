<script>
    function playSound(text) {
        var autofile = "http://tts.baidu.com/text2audio?lan=zh&ie=UTF-8&spd=5&text=" + text;
        var myAuto = document.getElementById('myAudio');
        myAuto.src = autofile;
    }
    function adminSiteInfo(){
        $.ajax({
            url:'/manager/adminSiteInfo/'
        }).done(function(data){
            BootstrapDialog.show({
                title: '【修改座机号或者密码】',
                message:  $('<div></div>').html(data),
                buttons: [{
                    label: 'Close',
                    action: function(dialogRef){
                        dialogRef.close();
                    }
                }]
            });
        });
    }

    function adminPassword(){
        layerOpenIframe('【修改密码】','/manager/adminPassword');
    }

    function adminPhone(){
        layerOpenIframe('【修改座机号】','/manager/adminPhone');
    }

    function clearRegCount() {
        $('#regCount').html('0');
        $('#regCount-ul').children().filter('li').remove();
    }

	function clearActiveCount() {
		$('#activeCount').html('0');
		$('#activeCount-ul').children().filter('li').remove();
	}

    function clearDepositCount() {
        $('#depositCount').html('0');
        $('#depositCount-ul').children().filter('li').remove();
    }

    function clearDrawCount() {
        $('#drawCount').html('0');
        $('#drawCount-ul').children().filter('li').remove();
    }
    function clearTaskCount() {
        $('#taskCount').html('0');
        $('#taskCount-ul').children().filter('li').remove();
    }

	function checkInfo(){
		ajaxurl = '/manager/checkInfo';
		_token  = '<?php echo e(csrf_token()); ?>';
		datastr = "_token=" + _token;
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: datastr,
			dataType: "json",
			success: function (data) {
				ifplay = false;
				playtext = "你有";
				if(data['withdrawCount']>0){
					clearDrawCount();
					$('#drawCount').html(data['withdrawCount']);
					$('#drawCount-ul').append(data['withdrawHtml']);
					playtext = playtext+""+data['withdrawCount']+"条取款信息未处理，";
					ifplay = true;
				}
				if(ifplay){
					playSound(playtext);
                }
			}
		});
	}


    let activeMenuItem = undefined;

    function clearActive(){
        if(activeMenuItem == undefined){
            return;
        }

        activeMenuItem.removeClass('active');
    }

    function addTabsLocal(id,title,url,link){
        var option={
            id:id,
            title:title,
            close:true,
            url:url
        };

        clearActive(link.parentElement);

        let elParent = $(link.parentElement);
        elParent.addClass('active');

        activeMenuItem = elParent;

        addTabs(option);
    }
    let option={
        id:'dashboard',
        title:'我的桌面',
        close:true,
        url:'/manager/dashboard'
    };
    addTabs(option);

    $("[class='modal fade']").on('show.bs.modal', function (e) {
        $(this).find('.modal-dialog').css({
            'margin-top': function () {
                return ($(window).height() / 4);
            }
        });
    });
    function centerModals(){
        $('.modal').each(function(i){
            var $clone = $(this).clone().css('display', 'block').appendTo('body');
            var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
            top = top > 0 ? top : 0;
            $clone.remove();
            $(this).find('.modal-content').css("margin-top", top);
        });
    }

    $(document).ready(function() {
        $('.modal').on('show.bs.modal', centerModals);
        $(window).on('resize', centerModals);
    });
</script>
<?php /**PATH D:\www\ganglan\resources\views/manager/layouts/script.blade.php ENDPATH**/ ?>