<?php if(!empty($page_title)): ?>
    <?php if('Search'== $page_title['type']): ?>
        <?php echo $__env->make($page_title['content'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php elseif('Title'== $page_title['type']): ?>
        <div></div>
    <?php else: ?>
        
    <?php endif; ?>
<?php endif; ?><?php /**PATH D:\www\ganglan\resources\views/manager/search.blade.php ENDPATH**/ ?>