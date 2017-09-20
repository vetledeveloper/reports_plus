<?php

/**
 * TimeReports filters home screen tab
 * 
 * @package custom.modules.time_reports_plus
 * @subpackage models
 */
class TimeReportsPlusHomescreenTab extends HomescreenTab {
	
	/**
	 * Return tab description
	 * 
	 * @return string
	 */
	function getDescription() {
		return lang ( 'Time Report Plus' );
	} // getDescription
	

	/**
	 * Render tab
	 * 
	 * @param IUser $user
	 * @return string
	 */
	function render(IUser $user) {
		AngieApplication::useHelper ( 'time_reports_plus_filters', REPORTS_PLUS_MODULE );
		$report_filter = $this->getReportFilterId () ? TimeReportsPlus::findById ( $this->getReportFilterId () ) : null;
		return smarty_function_time_reports_plus_filters ( array ('user' => &$user, 'filter' => $report_filter ), SmartyForAngie::getInstance () );
	
	} // render
	

	// ---------------------------------------------------
	//  Options
	// ---------------------------------------------------
	

	/**
	 * Returns true if this widget has additional options
	 * 
	 * @return boolean
	 */
	protected function hasOptions() {
		return true;
	} // hasOptions
	

	/**
	 * Render widget options form section
	 * 
	 * @param IUser $user
	 * @return string
	 */
	protected function renderOptions(IUser $user) {
		$reports = TrackingReportPlus::getIdNameMap ();
		$view = SmartyForAngie::getInstance ()->createTemplate ( AngieApplication::getViewPath ( 'time_reports_filters_options', 'homescreen_tabs', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
		
		$view->assign ( array ('homescreen_tab' => $this, 'user' => $user, 'homescreen_tab_data' => array ('report_filter_id' => $this->getReportFilterId () ) ) );
		return $view->fetch ();
	} // renderOptions
	

	/**
	 * Bulk set widget attributes
	 * 
	 * @param array $attributes
	 */
	function setAttributes($attributes) {
		$this->setReportFilterId ( isset ( $attributes ['report_filter_id'] ) ? $attributes ['report_filter_id'] : null );
		parent::setAttributes ( $attributes );
	} // setAttributes
	

	/**
	 * Return report_filter_id
	 * 
	 * @return int
	 */
	function getReportFilterId() {
		return $this->getAdditionalProperty ( 'report_filter_id' );
	} // getReportFilterId
	

	/**
	 * Set report_filter_id
	 * 
	 * @param int $value
	 * @return int
	 */
	function setReportFilterId($value) {
		return $this->setAdditionalProperty ( 'report_filter_id', $value );
	} // setReportFilterId


}