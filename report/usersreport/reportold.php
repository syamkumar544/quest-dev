<?php 
require_once('../../config.php');
require_once 'phpexcel/Classes/PHPExcel.php';
require_once('report_data.php');

global $PAGE,$DB,$CFG;

require_login();

$PAGE->set_context(context_system::instance());
$course_items = array();
$ci_cindex = array();
$attendance = array();
$attendance_courses = array();

if(isset($_POST['selected_users'])) {
    $where_usr_str = ' AND u.id IN ('.implode(',',$_POST['selected_users']).')';    
}else {
    $where_usr_str = '';
}

$category_id   = optional_param('category_id',0,PARAM_INT);
if(isset($_POST['sub_cat'])) {
   $categoryids = implode(',',$_POST['sub_cat']);
}
$selected_activities = optional_param_array('activities',0,PARAM_TEXT);

if(optional_param('date_flag',0,PARAM_INT)) {
    $start_date = $_POST['startdate']['month'].'/'.$_POST['startdate']['day'].'/'.$_POST['startdate']['year'];
    $start_date_time = strtotime($start_date);
    $end_date = $_POST['enddate']['month'].'/'.$_POST['enddate']['day'].'/'.$_POST['enddate']['year'];
    $end_date_time = strtotime($end_date);
    $where_string = " WHERE category IN (". $categoryids .") AND startdate BETWEEN '". $start_date_time ."' AND '". $end_date_time ."'";
    
}else {
    $where_string = " WHERE category IN (". $categoryids .") ";
}
$reportdata = new report_data();

$courses = $reportdata->getCategoryCourses($where_string);
$courseids = array_keys($courses);

if(count($courseids) > 0) {

$courseid_str = implode(',',$courseids);

list($item_ids, $course_items, $courses) = $reportdata->getCourseItems($courses, $selected_activities);

if(count($item_ids) > 0) {
    list($users, $course_users, $prefix_check) = $reportdata->getUsers($course_items, $where_usr_str);
    $userid_str = '';

    if($users) {
        $userids = array_keys($users);
        $userid_str = implode(',',$userids);

       /* if($CFG->report_users_show_user_profile_fields) {

            list($usr_info_cat, $usr_info_field_arr, $usr_info_data_arr) = $reportdata->getUserProfileFields($userid_str);
        }

        if($CFG->report_users_show_user_attendance) {

            list($attendance, $attendance_courses) = $reportdata->getUsersAttendance($courseid_str, $userid_str);

        }*/

        $items = $reportdata->userGrades($courseid_str, $userid_str, $userids, $scales, $item_ids);

    }
    if($CFG->report_users_show_user_profile_fields) {

       list($usr_info_cat, $usr_info_field_arr, $usr_info_data_arr) = $reportdata->getUserProfileFields($userid_str);
    }

    $num_of_users = count((array)$users);

    //PHP Excel Code to generate Excel Report
    $objPHPExcel = new PHPExcel();

    $row_pos = 2;

    $objPHPExcel = $reportdata->getbasicColumnsExcel($objPHPExcel, $num_of_users, $row_pos, $prefix_check);

    $styleArray = array('borders' => array(
                                'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,                            
                         ),
                   ),
            );
    if($prefix_check) {
        $c_pos = $last_pos = 5;
    }else {
        $c_pos = $last_pos = 4;
    }  
    if($CFG->report_users_show_user_profile_fields) {
        if($usr_info_cat) {
            list($objPHPExcel, $last_pos, $field_id_arr) = $reportdata->getUserProfileFieldsExcel($objPHPExcel, $usr_info_cat, $c_pos, $last_pos, $usr_info_field_arr, $num_of_users, $row_pos);
        }
    }
    list($objPHPExcel, $ci_cindex, $ic_inc, $all_courses_total) = $reportdata->getCoursesExcel($objPHPExcel, $courses, $course_items, $last_pos, $num_of_users, $row_pos, $attendance, $attendance_courses);

    $objPHPExcel = $reportdata->getOtherColumnsExcel($objPHPExcel, $ic_inc, $num_of_users);

    list($objPHPExcel, $user_all_course_total_arr, $max_marks) = $reportdata->getUsersGradesExcel($objPHPExcel, $users, $field_id_arr, $usr_info_data_arr, $items, $ci_cindex, $ic_inc, $all_courses_total);

    for($inc = 0; $inc < count($user_all_course_total_arr); $inc++) {
        $cgpa = ($user_all_course_total_arr[$inc]/$max_marks) * 100; 

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+2),($inc+4),round($cgpa));    
    }
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+2),3, $max_marks);

    $category_info = $DB->get_record('course_categories', array('id' => $category_id));
    $category_name = $category_info->name;

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$category_name.' Grades.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
 }else {
     $returnurl = "$CFG->wwwroot/report/usersreport/category.php?category_actvities=empty&";
     redirect($returnurl);
     
 }
}else {
    $returnurl = "$CFG->wwwroot/report/usersreport/category.php?category_courses=empty";
    redirect($returnurl);
}
?>
