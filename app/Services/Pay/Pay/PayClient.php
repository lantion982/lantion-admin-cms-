<?php
namespace App\Services\Pay\Pay;

//支付信息类

class PayClient{
    //用户ID
    public $memberId;
    public $login_name;
    public $companyId;
    //前端域名来自 header
		public $domain;
    //订单编号
    public $billNo;
    //充值金额
    public $depositMoney;
    //IP
    public $ip;
    public $zone;
    //存款时间
    public $depositTime;
    //支付方式
    public $paymentMethodCode;
    //二维码类型
    public $QRType;
    //支付平台
    public $paymentPlatformCode;
    //收款银行
    public $bankCode;
    //收款方账号
    public $paymentAccountId;
    //活动ID
    public $memberActivityId;
    //会员参与的活动
    public $activityId;
    //活动关联游戏厅
    public $roomCode;
    //第三方商户号
    public $accountNumber;
    //第三方需要的参数
    public $infoAcct;
    //是否移动端
    public $isMobile;
    //取款时间
    public $drawTime;
    //取款金额
    public $drawMoney;
    //代理ID
    public $agentId;
    //收付款银行卡ID
    public $bankAccountId;
    //汇款人姓名
    public $bankAccountName;
    //汇款银行卡号
    public $bankAccountNumber;
    //开户行省份
    public $openingBank;
    //汇款附言
    public $remittanceInfo;

    public function __construct(){}

}
