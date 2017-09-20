<?php

// Build on top of system module
AngieApplication::useController ( 'project_tracking', TRACKING_MODULE );
/**
 * TimeReportsPlus controller implementation
 *
 * @package custom.modules.time_report_plus
 * @subpackage controllerslIST
 * 
 * 
 */
class ProjectTrackingPlusController extends ProjectTrackingController {
	
	var $active_tracking_report;
	
	/**
	 * Active tracking report
	 *
	 * @var TimeReportsPlus
	 */
	/**
	 * Show project time and expenses log
	 */
	function log() {
		if (($this->logged_user->canUseReports () || $this->active_project->isLeader ( $this->logged_user )) && $this->request->isWebBrowser ()) {
			$this->wireframe->actions->add ( 'reports', lang ( 'Reports' ), Router::assemble ( 'project_tracking_tab', array ('project_slug' => $this->active_project->getSlug (), 'project_id' => $this->active_project->getId () ) ) );
		}
		parent::log ();
	} // log
	

	function render_tab() {

		$report_id = $this->request->getId ( 'tracking_report_id' );
		if ($report_id) {
			$this->active_tracking_report = TimeReportsPlus::findById ( $report_id );
		} else {
			$this->active_tracking_report = new TrackingReport ();
		}
		
		$project_user = $this->active_project->users ()->getForSelect ( $this->logged_user );
		$visible_companies_ids = (! $this->logged_user->canUseReports ()) ? DB::executeFirstColumn ( "SELECT company_id FROM " . TABLE_PREFIX . "users where id IN (?)", $this->active_project->users()->getIds()) : null;
		$companies = Companies::getIdNameMap ( $visible_companies_ids, STATE_VISIBLE);
		
		$this->response->assign ( array ('time_reports_plus_run' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('project_id' => $this->active_project->getId () ) ),
		'time_reports_plus' => TimeReportsPlus::findByUser ( 'TrackingReport', $this->logged_user ) ,
		'users' => $project_user, 
		'companies' => $companies,
		'projects' => Projects::getIdNameMap ( $this->logged_user, STATE_VISIBLE, null, null, true ), 
		'project_categories' => Categories::getIdNameMap ( null, 'ProjectCategory' ), 
		'job_types' => JobTypes::getIdNameMap (), 'expense_categories' => ExpenseCategories::getIdNameMap (), 
		'hide_project_filter' => true, 'export_csv_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => 'csv', 'project_id' => $this->active_project->getId () ) ), 
		'export_html_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => "html", 'project_id' => $this->active_project->getId () ) ), 
		'invoice_based_on_url' => (AngieApplication::isModuleLoaded ( 'invoicing' ) && Invoices::canAdd ( $this->logged_user )) ? extend_url ( $this->active_tracking_report->invoice ()->getUrl (), array ('filter[project_filter]' => 'selected', 'filter[project_ids][]' => $this->active_project->getId () ) ) : false, 
		'can_use_reports' => $this->logged_user->canUseReports () ) );
	
	}
}
