<div class="col-md-3 col-sm-6">
    <label class="hidden-sm hidden-xs">开始时间</label>
    <input name="startDate" id="startDate" class="form-control query_time" placeholder="开始时间"
           type="text" value="<?php echo e($startDate); ?>">
</div>
<div class="col-md-3 col-sm-6">
    <label class="hidden-sm hidden-xs">结束时间</label>
    <input name="endDate" id="endDate" class="form-control query_time" placeholder="结束时间"
           type="text" value="<?php echo e($endDate); ?>">
</div>
<div class="col-md-6 col-sm-6 col-xs-12 hidden-sm hidden-xs">
    <div class="btn-group">
        <label class="hidden-sm hidden-xs">快速选取</label>
        <div class="form-control no-border no-padding" id="day">
            <a href="#" class="btn btn-default" onclick="setdate(1,-1);" title="前一天">
                <i class="fa fa-angle-double-left"></i>
            </a>
            <a href="#" class="btn btn-default" onclick="setdate(1,0);">今天</a>
            <a href="#" class="btn btn-default" onclick="setdate(1,1);" title="后一天">
                <i class="fa fa-angle-double-right"></i>
            </a>
        </div>
    </div>
    <div class="btn-group">
        <label class="hidden-sm hidden-xs" style="margin-top:3px;">&nbsp;</label>
        <div class="form-control no-border no-padding">
            <input id="addweek" type="hidden" value="0">
            <a href="#" class="btn btn-default" onclick="setdate(2,-1);" title="上一周">
                <i class="fa fa-angle-double-left"></i>
            </a>
            <a href="#" class="btn btn-default" onclick="setdate(2,0);">本周</a>
            <a href="#" class="btn btn-default" onclick="setdate(2,1);" title="下一周">
                <i class="fa fa-angle-double-right"></i>
            </a>
        </div>
    </div>
    <div class="btn-group">
        <label class="hidden-sm hidden-xs" style="margin-top:3px;">&nbsp;</label>
        <div class="form-control no-border no-padding">
            <input id="addmonth" type="hidden" value="0">
            <a href="#" class="btn btn-default" onclick="setdate(3,-1);" title="上个月">
                <i class="fa fa-angle-double-left"></i>
            </a>
            <a href="#" class="btn btn-default" onclick="setdate(3,0);">本月</a>
            <a href="#" class="btn btn-default" onclick="setdate(3,1);" title="下个月">
                <i class="fa fa-angle-double-right"></i>
            </a>
        </div>
    </div>
</div>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/searchDateTime.blade.php ENDPATH**/ ?>