<?php
namespace App\Services\Pay\Pay;
//支付结果，获取的数据存放在 payResult 类中，

class PayResult{

    public $memberId;
    public $agentId;
    public $billNo;
    public $remittanceInfo;
    public $depositMoney;
    public $drawMoney;
    public $giftMoney;
    public $paymentPlatformCode;
    public $paymentMethodCode;
    public $dateTime;
    public $paymentQueueId;                     //在处理队列完成后，状态更新 HandelThirdCallBack中用到；
    public $allArgs;                            //第三方回调的所有参数
    public $payStatus;                          //支付成功||失败
    public $memberActivityId;                   //活动ID
    public $isContainActivity;                  //是否参加活动
    public $isCutoff;                           //参加活动的话是否截单

    public function __construct(){}

}