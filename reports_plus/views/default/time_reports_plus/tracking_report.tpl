    {AngieApplication::useWidget('tracking_report_results' , 'tracking')}
<div id="tracking_report"></div>

<script type="text/javascript">
  $('#tracking_report').trackingReportResults({
    'records' : {$records|json nofilter},
    'currencies' : {$currencies|json nofilter},
    'sum_by_user' : {$report->getSumByUser()|json nofilter},
    'group_by' : {$report->getGroupBy()|json nofilter},
    'show_time' : {$report->queryTimeRecords()|json nofilter},
    'show_expenses' : {$report->queryExpenses()|json nofilter}
  });
  $('#tracking_report').find('p').remove();
</script>