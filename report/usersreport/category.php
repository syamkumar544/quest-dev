<script src="javascript/jquery1.9-min.js"></script>
<script type="text/javascript" src="javascript/blocks.js"></script> 

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A report to display the outcome of users grades based on selected categories courses.
 *
 * @package    report
 * @subpackage usersreport
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('category_form.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
//require_once('report_html.php');
//$PAGE->requires->js('/javascript/listbox.js', true);

//admin_externalpage_setup('usersreport', '', null, '', array('pagelayout'=>'report'));

require_login();

$PAGE->set_url('/report/usersreport/category.php', $params);
$PAGE->set_pagelayout('report');

$ufiltering = new user_filtering();
// Display the Categories
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string("categories"));

if(optional_param('category_courses','',PARAM_TEXT) == 'empty') {
    echo '<div id="empty" style="color: #AA0000;margin-bottom:10px;">Selected Category does not have Courses.</div>';
}else if(optional_param('category_actvities','',PARAM_TEXT) == 'empty') {
    echo '<div id="empty" style="color: #AA0000;margin-bottom:10px;">Selected Category does not have activities.</div>';
}
$selected_category = optional_param('category_id',0,PARAM_INT);
$displaylist = array();
$parentlist = array();
$sub_categories = array();

make_categories_list($displaylist, $parentlist);//echo '<pre>'; print_r($parentlist);echo '</pre>';

if(!empty($selected_category)) {
  foreach($parentlist as $id => $parents) {
     if($parents[count($parents)-1] == $selected_category) {
        $sub_categories[$id] = $displaylist[$id];
     }
  }
}

foreach($displaylist as $key => $name) {
   if(count($parentlist[$key]) != 2) {
      unset($displaylist[$key]);
   } 
}

$admins = get_admins();
$isadmin = false;
foreach($admins as $admin) {
    if($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}
if(!$isadmin) {
   $sql = "SELECT c.instanceid FROM {role_assignments} ra JOIN {context} c ON ra.contextid = c.id 
           WHERE c.contextlevel = ".CONTEXT_COURSECAT." AND ra.userid = $USER->id";
   $usercategories = $DB->get_records_sql($sql);
   $category_ids = array_keys($usercategories);
   foreach($displaylist as $id=>$name) {
      $result = array_intersect($parentlist[$id],$category_ids);
      if(count($result) > 0) {
         $categories_list[$id] = $name;
      }
   }
}else {
    $categories_list = $displaylist;
}
$categories_list['0'] = "Select Category";

if(count($categories_list) < 1) {
    echo '<div style="color: #AA0000;margin-bottom:10px;">You are not enroled to any course.</div>';
}
$numusers = '';
$data = array();
if($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_GET['page']) && $_GET['page'] != NULL)) {
    
    if(!isset($_POST['startdate'])) {
        $pieces = explode(',',$_GET['startdate']);
        $_POST['startdate'] = array('day' => $pieces[0],
                                    'month' => $pieces[1],
                                    'year' => $pieces[2]);
    }
    if(!isset($_POST['enddate'])) {
        $pieces = explode(',',$_GET['enddate']);
        $_POST['enddate'] = array('day' => $pieces[0],
                                    'month' => $pieces[1],
                                    'year' => $pieces[2]);
    }
        
    $html_obj = new users_report_html();
    
    $order = optional_param('order', 0, PARAM_ALPHANUM);
    if(empty($order)) {
        $order = 'asc';
    }
    $sortitemid = optional_param('sortitemid', 0, PARAM_ALPHANUM);
    if(empty($sortitemid)) {
        $sortitemid = 'lastname';
    }
    if(isset($_POST['selected_users'])) {
        $selected_users = $_POST['selected_users'];
    }else {
        if(isset($_GET['selected_users'])) {
            $selected_users = explode(',',$_GET['selected_users']);
        }else {
            $selected_users = array();
        }
        
    }
    $page = optional_param('page',0,PARAM_INT);
    $selected_activities = optional_param_array('activities',0,PARAM_TEXT);
    
    $data = array('order' => $order,
                  'sortitemid' => $sortitemid,
                  'selected_users' => $selected_users,
                  'date_flag' => optional_param('date_flag',0,PARAM_INT),
                  'startdate' => $_POST['startdate'],
                  'enddate' => $_POST['enddate'],
                  'category_id' => optional_param('category_id',0,PARAM_INT),
                  'page' => $page,
                  'selected_activities' => $selected_activities);
    list($report_html,$numusers) = $html_obj->get_usersreport_html($data);
    $studentsperpage = 20;
    $url_params_array = array('order' => $order,
                              'sortitemid' => $sortitemid,
                              'selected_users' => implode(',',$selected_users),
                              'date_flag' => optional_param('date_flag',0,PARAM_INT),
                              'startdate' => implode(',',$_POST['startdate']),
                              'enddate' => implode(',',$_POST['enddate']),
                              'category_id' => optional_param('category_id',0,PARAM_INT),
                              'page' => $page);
}
$categoryform = new usersreport_category_form(NULL, array('selected_category' => $selected_category, 'sub_categories' => $sub_categories,'category'=> $categories_list,'uifilter' => get_selection_data($ufiltering),'date_filter' => $CFG->report_users_enable_date_filter,'user_filter' => $CFG->report_users_enable_users_filter,'activity_filter' => $CFG->report_users_enable_activity_filter,'postdata' => $data));

$form_data = $categoryform->get_data();

echo $categoryform->display();

if($numusers > 0) {
        echo '<input type="button" onclick=getreport("'. $sortitemid .'","'. $order .'"); value="Download Excel" style="float:right;"/>';
        echo '<div class="clearer"></div>';
        echo $OUTPUT->paging_bar($numusers, $page, $studentsperpage, new moodle_url('/report/usersreport/category.php',$url_params_array));
        echo $report_html;
        echo $OUTPUT->paging_bar($numusers, $page, $studentsperpage, new moodle_url('/report/usersreport/category.php',$url_params_array));
        echo '<input type="button" onclick=getreport("'. $sortitemid .'","'. $order .'"); value="Download Excel" style="float:right;"/>';
}

echo $OUTPUT->footer();
?>


