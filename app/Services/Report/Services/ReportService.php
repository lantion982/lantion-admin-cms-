<?php
//报表

namespace App\Services\Room\Services;

use App\Services\Report\Report\BaseReport;

class ReportService{

	protected $baseReport;

	public function __construct(BaseReport $baseReport){
		$this->baseReport = $baseReport;
	}

}