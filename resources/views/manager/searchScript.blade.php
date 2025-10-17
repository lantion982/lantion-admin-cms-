<script language="JavaScript">
    function clearForm(){
        $('#keyword').val('');
        $('#startDate').val('{{date('')}}');
        $('#endDate').val('{{date('')}}');
        $('#billNo').val('');
        /*$('#is_locked').each(function(i,j){
            $(j).find('option:selected').attr('selected',false);
            $(j).find('option').first().attr('selected',true);
        });*/
       /* $('#move_type').each(function(i,j){
            $(j).find('option:selected').attr('selected',false);
            $(j).find('option').first().attr('selected',true);
        });*/
        //bootstrap select
        $('#level_id').selectpicker('val','');
        $('#is_allow').selectpicker('val','');
        $('#move_type').selectpicker('val','');
        $('#deposit_status').selectpicker('val','');

        $('#page_count').each(function(i,j){
            $(j).find('option:selected').attr('selected',false);
            $(j).find('option').first().attr('selected',true);
        });
    }

    $(document).ready(function(){
        lay('.query_time').each(function(){
            laydate.render({
                elem:this,type:'datetime',trigger:'click'
            });
        });
        lay('.query_date').each(function(){
            laydate.render({
                elem:this,trigger:'click'
            });
        });
        $(document).keypress(function(e){
            if(!e){
                e = window.event;
            }
            if((e.keyCode||e.which)==13){
                $('#submitSearch').click();
                return false;
            }
        });
    });

    function setdate(a,b,dateonly = 0){
        datestar = $('#startDate').val();
        if(datestar==undefined||datestar==''){
            datestar = '{{now()}}';
        }
        if(b==0){
            datestar = '{{now()}}';
        }
        if(a==1){
            startime = getDate(b,datestar);
            endtime = getDate(b,datestar);
        }else if(a==2){
            if(b==0){
                $('#addweek').val(0);
            }else if(b== -1){
                b = $('#addweek').val();
                b = b-1;
            }else if(b==1){
                b = parseInt($('#addweek').val());
                b = b+1;
            }
            $('#addweek').val(b);
            startime = getWeek('s',b);
            endtime = getWeek('e',b);
        }else if(a==3){
            if(b==0){
                $('#addmonth').val(0);
            }else if(b== -1){
                b = parseInt($('#addmonth').val());
                b = b-1;
            }else if(b==1){
                b = parseInt($('#addmonth').val());
                b = b+1;
            }
            $('#addmonth').val(b);
            startime = getMonth('s',b);
            endtime = getMonth('e',b);
        }else if(a==4){
            if(b==0){
                $('#addyear').val(0);
                startime = '{{date('Y-01-01')}}';
                endtime = '{{date('Y-m-d')}}';
            }else if(b== -1){
                startime = '{{date("Y-01-01",strtotime("-1 year"))}}';
                endtime = '{{date('Y-12-31',strtotime("-1 year"))}}';
            }else if(b==1){
                startime = '{{date('Y-01-01',strtotime("+1 year"))}}';
                endtime = '{{date('Y-12-31',strtotime("+1 year"))}}';
            }
        }
        if(dateonly==0){
            startime = startime+' 00:00:00';
            endtime = endtime+' 23:59:59';
        }

        $('#startDate').val(startime);
        $('#endDate').val(endtime);
    }

    //dates为数字类型，0代表今日,-1代表昨日，1代表明日，返回yyyy-mm-dd格式字符串，dates不传默认代表今日
    function getDate(dates,nowstar){
        dd = new Date(nowstar);
        n = dates||0;
        dd.setDate(dd.getDate()+n);
        y = dd.getFullYear();
        m = dd.getMonth()+1;
        d = dd.getDate();
        m = m<10?'0'+m:m;
        d = d<10?'0'+d:d;
        days = y+'-'+m+'-'+d;
        return days;
    }

    //type为字符串类型，有两种选择，"s"代表开始,"e"代表结束，dates为数字类型，不传或0代表本周，-1代表上周，1代表下周
    function getWeek(type,dates){
        now = new Date();
        nowTime = now.getTime();
        day = now.getDay();
        longTime = 24*60*60*1000;
        n = longTime*7*(dates||0);
        if(type=='s'){
            dd = nowTime-(day-1)*longTime+n;
        }
        if(type=='e'){
            dd = nowTime+(7-day)*longTime+n;
        }
        dd = new Date(dd);
        let y = dd.getFullYear();
        let m = dd.getMonth()+1;
        let d = dd.getDate();
        m = m<10?'0'+m:m;
        d = d<10?'0'+d:d;
        days = y+'-'+m+'-'+d;
        return days;
    }

    //type为字符串类型，有两种选择，"s"代表开始,"e"代表结束，months为数字类型，不传或0代表本月，-1代表上月，1代表下月
    function getMonth(type,months){
        d = new Date();
        year = d.getFullYear();
        month = d.getMonth()+1;
        if(Math.abs(months)>12){
            months = months%12;
        }
        if(months!=0){
            if(month+months>12){
                year++;
                month = (month+months)%12;
            }else if(month+months<1){
                year--;
                month = 12+month+months;
            }else{
                month = month+months;
            }
        }
        month = month<10?'0'+month:month;
        date = d.getDate();
        firstday = year+'-'+month+'-'+'01';
        lastday = '';
        if(month=='01'||month=='03'||month=='05'||month=='07'||month=='08'||month=='10'||month=='12'){
            lastday = year+'-'+month+'-'+31;
        }else if(month=='02'){
            if((year%4==0&&year%100!=0)||(year%100==0&&year%400==0)){
                lastday = year+'-'+month+'-'+29;
            }else{
                lastday = year+'-'+month+'-'+28;
            }
        }else{
            lastday = year+'-'+month+'-'+30;
        }
        days = '';
        if(type=='s'){
            days = firstday;
        }else{
            days = lastday;
        }
        return days;
    }
</script>
