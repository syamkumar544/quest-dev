<?php
require_once '../config.php';
require_once 'Classes/PHPExcel.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';
$courses = $DB->get_records('course', array('category' => 5));
$courseids = array_keys($courses);
$courseid_str = implode(',',$courseids);

$courseids = array_keys($courses);


/*$userobj = $report->load_users();
$numusers = $report->get_numusers();
$report->load_final_grades();
$reporthtml = $report->get_grade_table();echo $reporthtml;print_R($courseids);*/
foreach($courseids as $courseid) {

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'grader', 'courseid'=>$courseid, 'page'=>$page));
$context = context_course::instance($courseid);
$sortitemid = '';
$report = new grade_report_grader($courseid, $gpr, $context, $page, $sortitemid);
$gtree = new grade_tree($courseid, true);
$gtree_grade_items = $gtree->get_items();
foreach ($gtree->get_levels() as $key=>$row) {
foreach ($row as $columnkey => $element) {
if($element['type'] == 'item') {

    $course_items[$courseid][] = array('item_id' => $element['object']->id,'item_name' => $element['object']->get_name()); 
}
}
}
//print_r($course_items);
//print_r($report->get_left_rows());

$sql = "SELECT u.id, u.idnumber, u.picture, u.firstname, u.lastname, u.imagealt, u.email
FROM mdl_user u
JOIN (

SELECT DISTINCT eu1_u.id
FROM mdl_user eu1_u
JOIN mdl_user_enrolments eu1_ue ON eu1_ue.userid = eu1_u.id
JOIN mdl_enrol eu1_e ON ( eu1_e.id = eu1_ue.enrolid
AND eu1_e.courseid IN (".$courseid_str.") )
WHERE eu1_u.deleted =0
AND eu1_u.id <>1
)je ON je.id = u.id
JOIN (

SELECT DISTINCT ra.userid
FROM mdl_role_assignments ra

)rainner ON rainner.userid = u.id
AND u.deleted =0
ORDER BY u.lastname ASC , u.firstname ASC";
$users = $DB->get_records_sql($sql);
$userids = array_keys($users);
$userid_str = implode(',',$userids);


$sql = "SELECT g . *,gi.courseid,gi.id AS itemid,gi.itemtype
FROM mdl_grade_items gi, mdl_grade_grades g
WHERE g.itemid = gi.id
AND gi.courseid IN(". $courseid_str .")
AND g.userid
IN (". $userid_str .")";

$grades = $DB->get_records_sql($sql);
$items = array();

foreach($grades as $graderec) {
if(in_array($graderec->userid, $userids) && $graderec->itemtype == 'mod') {
    $item = $gtree_grade_items[$graderec->itemid];
    
    $items[$graderec->userid][$graderec->courseid][$graderec->itemid] = array('grade' => $graderec->finalgrade);
}
}
foreach($users as $user) {
$user_grades = array();
if(array_key_exists($user->id, $items)) {
$user_grades = $items[$user->id];
}else {
 $user_grades = array();
}
$users_data[] = array('id' => $user->id,
                      'idnumber' => $user->idnumber,
                      'firstname' => $user->firstname,
                      'lastname' => $user->lastname,
                      'email' => $user->email,
                      'items' => $user_grades);
}


}
//echo '<pre>'; print_R($users_data); echo '</pre>';

$objPHPExcel = new PHPExcel();print_r($gtree_grade_items);
$objPHPExcel->setActiveSheetIndex(0)
    	        ->setCellValue('A1', 'First Name')
        	    ->setCellValue('B1', 'Last Name')
            	->setCellValue('C1', 'ID')
	            ->setCellValue('D1', 'Email');
$cc_inc = 4; $ic_inc = 4;
foreach($courses as $courserec) {
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cc_inc,1, $courserec->fullname);
$start_point = $cc_inc;$num_items = 0; 
foreach($course_items[$courserec->id] as $itemrec) {
$cc_inc++;
$ci_cindex[$courserec->id][$itemrec['item_id']] = $ic_inc;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,2, $itemrec['item_name']);
$ic_inc++;
$num_items++;
}
//echo $courserec->fullname.'-'.$num_items.'<br>';

//echo PHPExcel_Cell::stringFromColumnIndex($start_point).'1:'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).'1'.'<br>';

if($num_items > 0) {
$objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($start_point).'1:'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).'1');

$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($start_point).'1:'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}
}
$r_inc = 3;
foreach ($users_data as $user_data)  {
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$r_inc, $user_data['firstname']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$r_inc, $user_data['lastname']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$r_inc, $user_data['id']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$r_inc, $user_data['email']);

foreach($user_data['items'] as $courseid => $user_items) {
foreach($user_items as $key => $user_item) {
if(isset($ci_cindex[$courseid][$key]))
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ci_cindex[$courseid][$key],$r_inc, $user_item['grade']);
}
}
$r_inc++;
}
/*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="GradeReport.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactprint_r($gtree_grade_items);ory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');*/
/*echo "SELECT u.id, u.picture, u.firstname, u.lastname, u.imagealt, u.email
FROM mdl_user u
JOIN (

SELECT DISTINCT eu1_u.id
FROM mdl_user eu1_u
JOIN mdl_user_enrolments eu1_ue ON eu1_ue.userid = eu1_u.id
JOIN mdl_enrol eu1_e ON ( eu1_e.id = eu1_ue.enrolid
AND eu1_e.courseid =2 )eu1_u
WHERE eu1_u.deleted =0
AND eu1_u.id <>1
)je ON je.id = u.id
JOIN (

SELECT DISTINCT ra.userideu1_u
FROM mdl_role_assignments ra
WHERE ra.roleid
IN ( 5 )
AND ra.contextid
IN ( 16, 3, 1 )
)rainner ON rainner.userid = u.id
AND u.deleted =0print_r($gtree_grade_items);
ORDER BY u.lastname ASC , u.firstname ASC
LIMIT 0 , 30";echo "<br>";

echo "SELECT u.id, u.picture, u.firstnameu1_ue, u.lastname, u.imagealt, u.email
FROM mdl_user u
JOIN (

SELECT DISTINCT eu1_u.id
FROM mdl_user eu1_u
JOIN mdl_user_enrolments eu1_ue ON eu1_ue.userid = eu1_u.id
JOIN mdl_enrol eu1_e ON ( eu1_e.id = eu1_ue.enrolid
AND eu1_e.courseid IN (".$courseid_str.") )
WHERE eu1_u.deleted =0
AND eu1_u.id <>1
)je ON je.id = u.id
JOIN (

SELECT DISTINCT ra.userid
FROM mdl_role_assignments ra
WHERE ra.roleid
IN ( 5 )
AND ra.contextid
IN ( 16, 3, 1 )
)rainner ON rainner.userid = u.idprint_r($gtree_grade_items);
AND u.deleted =0
ORDER BY u.lastname ASC , u.firstname ASC
LIMIT 0 , 30";echo "<br>";
echo 'SELECT g . *
FROM mdl_grade_items gi, mdl_grade_grades g
WHERE g.itemid = gi.id
AND gi.courseid =2
AND g.userid
IN ( 3, 4, 5, 6 )
LIMIT 0 , 30';

echo "SELECT * FROM `mdl_grade_items` WHERE `itemtype` = 'mod' and `itemmodule` = 'assign' and 
`iteminstance` = '62' and courseid = '9'";216

SELECT *
FROM `mdl_grade_grades`
WHERE `itemid` =216
AND `userid` =180*/
?>
