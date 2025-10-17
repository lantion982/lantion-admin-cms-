<?php

namespace App\Services\Pay\Services;

use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;

class PayService{

    protected $basePay;

    //调用的是 BasePay 的方法
    public function __construct(BasePay $basePay){
        $this->basePay = $basePay;
    }

    //获取所有支付方式，可能被废弃或者暂停使用
    public function getValidPaymentMethods(){
        return $this->basePay->getValidPaymentMethods();
    }

    //获取该会员支持的 充值方式；注意返回的要是 array
    public function getPaymentMethodsByMemberId($memberId){
        return $this->basePay->getPaymentMethodsByMemberId($memberId);
    }

    public function getAllowDepositBanks(){
        return $this->basePay->getAllowDepositBanks();
    }

    public function getAllowDrawBanks(){
        return $this->basePay->getAllowDrawBanks();
    }

    //查询某会员当前是否存在提款申请
    public function getAppliedBankDepositByMemberId($memberId){
        return $this->basePay->getAppliedBankDepositByMemberId($memberId);
    }

   //生成订单方法
    public function getValidBillNo($businessCode){
        return $this->basePay->getValidBillNo($businessCode);
    }

    //存款附言 6位随机大写字母
    public function remittance(){
        return $this->basePay->remittance();
    }

    //创建渠道对象，依赖注入
    public function getPaymentAccountByPayClient(PayClient $payClient){
        return $this->basePay->getPaymentAccountByPayClient($payClient);
    }

    //获取支付方式
    public function getPaymentAccountMethodByPayClient(PayClient $payClient){
        return $this->basePay->getPaymentAccountMethodByPayClient($payClient);
    }

    public function getPaymentAccountMethod(PayClient $payClient){
        return $this->basePay->getPaymentAccountMethod($payClient);
    }

    //对金额进行检查，返回数组
    public function checkMoneyNeedByByPayClient(PayClient $payClient){
        return $this->basePay->checkMoneyNeedByByPayClient($payClient);
    }

    //根据支付结果，给付金额并记录日志
    public function addDepositMoneyAndRecordMovement(PayResult $payResult){
        return $this->basePay->addDepositMoneyAndRecordMovement($payResult);
    }

    public function addGiftMoneyAndRecordMovement(PayResult $payResult){
        return $this->basePay->addGiftMoneyAndRecordMovement($payResult);
    }

    public function addGiftMoneyAndRecordMovementForManualActivity(PayResult $payResult){
        return $this->basePay->addGiftMoneyAndRecordMovementForManualActivity($payResult);
    }

    //申请取款
    public function genDrawApply($request){
        return $this->basePay->genDrawApply($request);
    }

    //取消取款申请
    public function cancelDrawApplyToApi($bill_no){
        return $this->basePay->cancelDrawApplyToApi($bill_no);
    }

}
