<?php
defined('MOODLE_INTERNAL') || die();

class block_login_tracker extends block_base {

    public function init() {
        $this->title = 'My Login Tracker';
    }

    public function get_content() {
        global $DB, $USER, $OUTPUT, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // 1. Get current parameters from URL (Default to current year/month if not set)
        $current_year  = date('Y');
        $current_month = date('n');

        $view  = optional_param('lt_view', 'year', PARAM_ALPHA); // 'year' or 'month'
        $year  = optional_param('lt_year', $current_year, PARAM_INT);
        $month = optional_param('lt_month', $current_month, PARAM_INT);

        // Base URL for navigation links
        $baseurl = $PAGE->url;

        // 2. Setup Data Structures based on the view
        $labels = [];
        $data = [];

        if ($view === 'year') {
            // VIEW: Show logins per month for a given year
            $this->content->text .= "<h3>Logins in {$year}</h3>";
            
            // Initialize 12 months with 0 logins using zero-based indexing
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('F', mktime(0, 0, 0, $m, 1));
                $data[$m - 1] = 0;
            }

            // Query Moodle logs for the specified year
            $start_time = mktime(0, 0, 0, 1, 1, $year);
            $end_time   = mktime(23, 59, 59, 12, 31, $year);

            // Fetch the matching login events and group by month in PHP.
            // Moodle stores timecreated as a Unix timestamp integer, not as a SQL timestamp.
            $sql = "SELECT id, timecreated
                      FROM {logstore_standard_log}
                     WHERE userid = :userid
                       AND action = :action
                       AND timecreated >= :starttime
                       AND timecreated <= :endtime
                  ORDER BY timecreated ASC";
            $params = [
                'userid' => $USER->id,
                'action' => 'loggedin',
                'starttime' => $start_time,
                'endtime' => $end_time,
            ];

            $records = $DB->get_recordset_sql($sql, $params);

            // Map records to zero-indexed array matching the labels.
            foreach ($records as $rec) {
                $m_index = (int)date('n', $rec->timecreated) - 1;
                if (isset($data[$m_index])) {
                    $data[$m_index]++;
                }
            }
            $records->close();
            $data = array_values($data); // Safeguard array alignment

            // Add navigation links to drill down into months
            $this->content->text .= '<div class="mb-2 text-center">';
            for ($m = 1; $m <= 12; $m++) {
                $m_name = date('M', mktime(0, 0, 0, $m, 1));
                $url = new moodle_url($baseurl, ['lt_view' => 'month', 'lt_year' => $year, 'lt_month' => $m]);
                $this->content->text .= html_writer::link($url, $m_name, ['class' => 'badge badge-info mr-1']);
            }
            $this->content->text .= '</div>';

        } else {
            // VIEW: Show logins per day for a given month
            $month_name = date('F', mktime(0, 0, 0, $month, 1));
            $this->content->text .= "<h3>Logins in {$month_name} {$year}</h3>";
            
            // Initialize days in month using zero-based indexing
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($d = 1; $d <= $days_in_month; $d++) {
                $labels[] = $d;
                $data[$d - 1] = 0;
            }

            $start_time = mktime(0, 0, 0, $month, 1, $year);
            $end_time   = mktime(23, 59, 59, $month, $days_in_month, $year);

            // Fetch the matching login events and group by day in PHP.
            // Moodle stores timecreated as a Unix timestamp integer, not as a SQL timestamp.
            $sql = "SELECT id, timecreated
                      FROM {logstore_standard_log}
                     WHERE userid = :userid
                       AND action = :action
                       AND timecreated >= :starttime
                       AND timecreated <= :endtime
                  ORDER BY timecreated ASC";
            $params = [
                'userid' => $USER->id,
                'action' => 'loggedin',
                'starttime' => $start_time,
                'endtime' => $end_time,
            ];

            $records = $DB->get_recordset_sql($sql, $params);

            // Map records to zero-indexed array matching the labels.
            foreach ($records as $rec) {
                $d_index = (int)date('j', $rec->timecreated) - 1;
                if (isset($data[$d_index])) {
                    $data[$d_index]++;
                }
            }
            $records->close();
            $data = array_values($data);

            // Back to year view link
            $back_url = new moodle_url($baseurl, ['lt_view' => 'year', 'lt_year' => $year]);
            $this->content->text .= html_writer::link($back_url, '← Back to Year View', ['class' => 'btn btn-secondary btn-sm mb-2']);
        }

        // 3. Render the Moodle Bar Chart
        $chart = new core\chart_bar();
        $series = new core\chart_series('Logins', $data);
        $chart->add_series($series);
        $chart->set_labels($labels);

        // Output the chart HTML directly into our block content
        $this->content->text .= $OUTPUT->render($chart);

        return $this->content;
    }
}