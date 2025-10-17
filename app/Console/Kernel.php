<?php
/*定时任务*/
namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel{
	//命令类注册
	protected $commands = [
		\App\Console\Commands\GetResult::class,
	];
	
	//定义命令调度
	protected function schedule(Schedule $schedule){
		$schedule->command('GetResult')->everyMinute()->between('21:10','21:55');
	}
	
	//注册应用程序的命令
	protected function commands(){
		$this->load(__DIR__.'/Commands');
		require base_path('routes/console.php');
	}
}
