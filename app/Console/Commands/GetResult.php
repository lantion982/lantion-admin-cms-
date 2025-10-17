<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;
use Log;

class GetResult extends Command{

    protected $signature   = 'GetResult';
    protected $description = '更新开奖结果';

    public function __construct(){
        parent::__construct();
    }

    public function handle(){
	    $games  = Game::where('is_enable',1)->get();
	    foreach($games as $game){
		    $res = updateResult($game->id);
	    }
    }
}
