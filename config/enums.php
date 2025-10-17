<?php
return [
	
	//分页列表
    'page_count' => [ '20','50','100','200','500' ,'2000','5000'],
    //会员登录结果
    'login_result'    => ['success' => '登录成功','failed' => '登录失败'],
	'news_appearance' => ['popup' => '弹框' , 'banner' => '滚动'],
	
	
	
    //状态颜色表
    'status_color' => [
        'apply'    => 'text-blue',
        'accept'   => 'text-green',
        'audit'    => 'text-black',
        'success'  => 'text-red',
        'failed'   => 'text-gray',
        'reject'   => 'text-yellow',
        'cancel'   => 'text-purple',
    ],
    
    //取款状态
    'draw_status' => ['apply' => '申请中','accept' => '已接受','audit' => '处理中','success' => '成功','failed' => '失败','reject' => '拒绝','cancel' => '已取消'],
    
    //新闻展现类型
    'show_type' => ['slider' => '轮播','discount' => '优惠','popup' => '弹框','banner' => '横幅',],

    // 权限类型：用于权限关联
    'permission_type'  => [ 'menu' => '菜单','page' => '页面','func' => '操作',],

    // 会员等级code ：枚举类型作为主键
    'level_code'  => [
        'level_1' => 'level_1',
        'level_2' => 'level_2',
        'level_3' => 'level_3',
        'level_4' => 'level_4',
        'level_5' => 'level_5',
        'level_6' => 'level_6',
        'level_7' => 'level_7',
        'level_8' => 'level_8',
    ],

    'debuffs_type' => ['gift_money'=>'赠金','backwater'=>'返水'],
	
    //全系统的银行code：
    'bank_code' => [
        'CCB'  => '建设银行',
        'ABC'  => '农业银行',
        'ICBC' => '工商银行',
        'BOC'  => '中国银行',
        'CMBC' => '民生银行',
        'CMB'  => '招商银行',
        'CIB'  => '兴业银行',
        'BOB'  => '北京银行',
        'BCM'  => '交通银行',
        'CEB'  => '光大银行',
        'GDB'  => '广发银行',
        'SPDB' => '浦发银行',
        'SDB'  => '深发银行',
        'PSBC' => '邮政银行',
        'HXB'  => '华夏银行',
        'CNCB' => '中信银行',
        'PAB'  => '平安银行',
        'TZB'  => '台州银行',
        'CZB'  => '浙商银行',
    ],
  
    //订单的状态
    'order_status'  => [
        'applied' => '申请中',
        'audit'   => '处理中',
        'succeed' => '成功',
        'fail'    => '失败',
        'cancel'  => '已取消',
        'expired' => '已过期'
    ],

    'queue_status_color'   => [
        'queue_no_enter' => 'text-gray',
        'queue_ready'    => 'text-green',
        'queue_succeed'  => 'text-red',
        'queue_failed'   => 'text-aqua',
        'queue_illegal'  => 'text-orange',
    ],

    //队列的状态
    'queue_status' => [
        'queue_no_enter' => '没有加入',
        'queue_ready'    => '准备执行',
        'queue_succeed'  => '执行成功',
        'queue_failed'   => '执行失败',
        'queue_illegal'  => '非法请求',
    ],
	
    //金额变动类型
    'move_type' => [
	    'bet_money'            => '投注扣除',
	    'win_money'            => '中奖结算',
	    'water_settle'         => '返水结算',
	    'water_recovery'       => '回收返水',
	    'brokerage_settle'     => '派发佣金',
        'money_freeze'         => '冻结金额',
        'money_unfreeze'       => '解冻金额',
        'money_draw'           => '取款扣除',
        'draw_fee'             => '手续费',
        'gift_money'           => '赠金',
        'admin_money_inc'      => '管理赠金',
        'admin_money_dec'      => '管理扣除',
    ],

    'admin_operate_type'  => [
        'admin_money_inc' => '管理增加',
        'admin_money_dec' => '管理减少',
    ],
	
    'bool_for_select' => [''  => '不限制','1' => '是','0' => '否',],
	'bool_for_sel'    => ['1' => '是','0' => '否',],
    'active_status'   => ['1' => '是','0' => '否',],

    //佣金、返水结算状态
    'settle_status' => ['wait' => '未结算', 'settled'=> '已结算','recovery' => '已回收'
    ],
 
    //佣金结算状态
    'brokerage_status' => [
        '0'           => '全部',
        'wait'        => '待处理',
        'settled'     => '已结算',
        'owe'         => '挂账',
        'owe_settled' => '挂账结清',
        'combine'     => '已累计到下期',
        'erase'       => '已抹除',
    ],

    'block_type' => ['white'=>'白名单','black'=>'黑名单',],

    'host_type'  => ['web'=>'后台','api'=>'API请求'],

    'member_admin_type' => ['App\Models\Admin'=>'管理员','App\Models\Member'=>'会员',],

    //订单状态颜色
    'mall_order_status_color' => [
        'unpaid'  => 'text-yellow',
        'paid'    => 'text-red',
        'cancel'  => 'text-gray',
        'audit'   => 'text-blue',
        'shipped' => 'text-green',
        'signFor' => 'text-purple',
        'return'  => 'text-black',
        'noGoods' => 'text-aqua',
    ],

    //订单状态
    'mall_order_status' => [
        'unpaid'  => '未付款',
        'paid'    => '已付款',
        'audit'   => '处理中',
        'shipped' => '已发货',
        'signFor' => '已收货',
        'return'  => '退货',
        'cancel'  => '已取消',
        'noGoods' => '无货',
    ],

    //权限分类
    'auth_type'  => ['menu'=>'菜单','page'=>'页面','func'=>'方法'],
    //下载资源VIP等级
    'down_vip'   => ['0'=>'免费','1'=>'月VIP','2'=>'年VIP','3'=>'终身VIP']
];
