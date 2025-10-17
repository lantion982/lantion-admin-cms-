<?php
//短信

namespace App\Services\SMS\Services;

use App\Services\SMS\Http\Http;
use Illuminate\Support\Facades\Config;

class SMSService{

	protected $http;
	protected $apikey;
	protected $sendUrl;
	protected $sendText;

	public function __construct(Http $http){
		$this->http = $http;
		$this->apikey = config('andySMS.smsApiKey');
		$this->sendUrl = config('andySMS.smsSendUrl');
		$this->sendTextPrefix = config('andySMS.smsSendTextPrefix');
		$this->sendTextSuffix = config('andySMS.smsSendTextSuffix');
	}

	public function sendSMS($mobile,$content){
		$apiKey  = $this->apikey;
		$message = $this->sendTextPrefix.$content.$this->sendTextSuffix;
		$options = array(
			'headers' => array(
				'Accept:application/json;charset=utf-8;',
				'Content-Type:application/x-www-form-urlencoded;charset=utf-8;',
			),
			'strings' => true
		);
		$response = $this->http->post($this->sendUrl,$apiKey,$message,$mobile,$options);
		return $response['result'];
	}
}
