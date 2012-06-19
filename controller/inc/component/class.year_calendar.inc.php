<?php
phpgw::import_class('controller.socheck_list');
include_class('controller', 'date_generator', 'inc/component/');
include_class('controller', 'check_list_status_info', 'inc/helper/');
include_class('controller', 'check_list_status_manager', 'inc/helper/');
	

class year_calendar {
	private $year;
	private $control;
	private $type;
	private $component;
	private $location_code;
	
	private $calendar_array = array();
	
  public function __construct($control, $year, $component, $location_code, $type){
    $this->year = $year;
    $this->control = $control;
    $this->component = $component;
    $this->location_code = $location_code;
    $this->type = $type;
        
    $this->init_calendar();
  }
   	   	
  /* Initializes calendar by setting status for each month in calendar array. 
   * 	- CONTROL_NOT_DONE if month date is in the past 
   * 	- CONTROL_REGISTERED if month date is in the future
   */ 
	function init_calendar(){
    for($i = 1;$i <= 12;$i++){
		  $this->calendar_array[$i] = null;
    }
		
    $ctr_start_date_ts = $this->control->get_start_date();
    $ctr_end_date_ts = $this->control->get_end_date();
    $period_start_date_ts = $this->get_start_date_year_ts($this->year);
    $period_end_date_ts = $this->get_start_date_year_ts($this->year+1);
    $repeat_type = $this->control->get_repeat_type();
    $repeat_interval = $this->control->get_repeat_interval();
    
    $date_generator = new date_generator($ctr_start_date_ts, $ctr_end_date_ts, $period_start_date_ts, $period_end_date_ts, $repeat_type, $repeat_interval);
    $dates_array = $date_generator->get_dates();
		
    // Inserts dates 
    foreach($dates_array as $date_ts){
      $todays_date_ts = $this->get_todays_date_ts();

      if($date_ts < $todays_date_ts){
        $status = "CONTROL_NOT_DONE";
      }else{
        $status = "CONTROL_REGISTERED";
      }
			
      $this->calendar_array[ date("n", $date_ts) ]["status"]  = $status;
      
      if($this->type = "component"){
      	$this->calendar_array[ date("n", $date_ts) ]["info"]  = array(
      																																	"date" => $date_ts, 
      																																	"control_id" => $this->control->get_id(),
      																																	"type" => "component", 
      																																	"component" = $component->toArray() 
      																																);
      }else{
      	$this->calendar_array[ date("n", $date_ts) ]["info"]  = array(
      																																	"date" => $date_ts, 
      																																	"control_id" => $this->control->get_id(),
      																																	"type" => "location", 
      																																	"location_code" = $location_code 
      																																);
      }
		}
	}
   	
	public function build_calendar( $check_lists_array ){
		
		foreach($check_lists_array as $check_list){
								
			$check_list_status_manager = new check_list_status_manager( $check_list );
			$check_list_status_info = $check_list_status_manager->get_status_for_check_list(); 
							
			$this->calendar_array[ date("n", $check_list_status_info->get_deadline_date_ts()) ]["status"]  = $check_list_status_info->get_status();
			$this->calendar_array[ date("n", $check_list_status_info->get_deadline_date_ts()) ]["info"]  = $check_list_status_info->serialize();
		}
		
		return $this->calendar_array;
	}
	
	public function build_agg_month_calendar( $agg_open_cases_pr_month_array ){
		
		foreach($agg_open_cases_pr_month_array as $status_agg_month_info)
		{
			$status = "CONTROLS_DONE_WITH_ERRORS";
					
			$this->calendar_array[$status_agg_month_info->get_month_nr()]["status"] = $status;
			$this->calendar_array[$status_agg_month_info->get_month_nr()]["info"]["agg_open_errors"] = $status_agg_month_info->get_agg_open_cases();
		}
					
		return $this->calendar_array;
	}
	
	public static function get_heading_array(){
			
		$heading_array = array();
		
		for($i=1;$i<=12;$i++){
			$heading_array[$i] = "$i";	
		}
		
		return $heading_array;
	}
	
	public static function get_todays_date_ts(){
		$todays_date_ts = mktime(0,0,0,date("m"), date("d"), date("Y"));
		return $todays_date_ts;
	}
	
	public static function get_start_date_year_ts($year){
	  $start_date_year_ts = strtotime("01/01/$year");
			
		return $start_date_year_ts;
	}
}