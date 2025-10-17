<?php

namespace App\Jobs;

use App\Libs\Helper;
use App\Models\GameBet;
use App\Models\Member;
use App\Models\QueueBet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue{
	use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

	private $queue_id;

	public function __construct($queue_id){
		$this->queue_id = $queue_id;
	}

	public function handle(){
		Log::info('队列测试=>param:'.$this->queue_id);
	}
}