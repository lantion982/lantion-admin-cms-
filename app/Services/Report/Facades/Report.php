<?php
//报表

namespace App\Services\Room\Facades;

use Illuminate\Support\Facades\Facade;

class Report extends Facade{

	protected static function getFacadeAccessor(){
		return 'ReportService';
	}
}