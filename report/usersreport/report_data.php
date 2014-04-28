<?php
require_once('../../config.php');
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

class report_data {
    
    /*
     * Get Category Courses
     * @param $where_string is filters based on date
     */
    public function getCategoryCourses($where_string) {       
        
        global $DB;        
        
        $sql = "SELECT id,shortname,category FROM {course} ". $where_string . " ORDER BY category";
        
        $courses = $DB->get_records_sql($sql);
        
        return $courses;
    }
    
    /*
     * Get Course Items
     * @param $courseids is array of course ids
     */
    public function getCourseItems($courses, $selected_activities = array()) {
        global $DB;

        $itemids = array();
        $module_order_str = ''; $selected_activities_str = '';
        $activities_order = array("scorm", "attforblock", "quiz", "assign");       
        
        foreach ($activities_order as $module) {
            $module_order_str .= "'" . $module . "',";
        }
        $module_order_str = substr($module_order_str, 0, -1);
        if($selected_activities) {
            foreach ($selected_activities as $module) {
                $selected_activities_str .= "'" . $module . "',";
            }
            $selected_activities_str = substr($selected_activities_str, 0, -1);
            $where_str = " AND (itemmodule IN ($selected_activities_str) OR itemmodule is NULL)";
        }else {
            $where_str = '';
        }
                
        foreach ($courses as $courseid => $course) {
            $sql = "SELECT id AS itemid, courseid, itemmodule, itemname, grademax, iteminstance, itemtype, itemmodule, scaleid
                    FROM {grade_items}
                    WHERE courseid = $courseid $where_str
                    ORDER BY FIELD( itemmodule, " . $module_order_str . " ) DESC";
            $items = $DB->get_records_sql($sql);
            if($items) {
                $course_items[$course->category][$courseid] = $items;
                $itemids = array_merge($itemids, array_keys($course_items[$course->category][$courseid]));
            }else {
                unset($courses[$courseid]);
            }
            
        }
        
        return array($itemids, $course_items, $courses);           
 
    }
    
    /*
     * Get Users 
     * @param $courseid_str is string of course ids seperated by ','
     * @param $where_usr_str is selected uses
     */
    public function getUsers($courseitems, $where_usr_str, $order_by_str = null, $limit_str = null) {
        
        global $DB, $CFG;
        $prefix = '';
        $prefix_check = false; $userids = array();
        $sql = "SELECT `COLUMN_NAME`
                FROM `INFORMATION_SCHEMA`.`COLUMNS`
                WHERE `TABLE_SCHEMA`='". $CFG->dbname ."' AND `TABLE_NAME` = '{user}'";
        $columns = $DB->get_records_sql($sql);        
        foreach($columns as $column) {
            
            if($column->column_name == 'prefix') {
                $prefix_check = true;
                $prefix = ', prefix';
                break;
            }
        }
        
        if($order_by_str != null) {
            $where_usr_str .= $order_by_str.$limit_str;
        }else {
            $where_usr_str .= ' ORDER BY u.lastname ASC , u.firstname ASC';
        }
        foreach ($courseitems as $category => $courses) {
            $courseid_str = implode(',',array_keys($courses));
            $sql = "SELECT DISTINCT eu1_u.id
                FROM {user} eu1_u
                JOIN {user_enrolments} eu1_ue ON eu1_ue.userid = eu1_u.id
                JOIN {enrol} eu1_e ON ( eu1_e.id = eu1_ue.enrolid
                AND eu1_e.courseid IN (".$courseid_str.") )
                WHERE eu1_u.deleted =0
                AND eu1_u.id <>1
                AND eu1_u.deleted =0 ";
           $course_users[$category] = $DB->get_records_sql($sql);
           $userids = array_merge($userids, array_keys($course_users[$category]));
           
        }
        array_unique($userids); $userids = array_values($userids);
        $sql = "SELECT id, idnumber, firstname, lastname, email $prefix FROM {user} WHERE id IN(".implode(',',$userids).")";

        $users = $DB->get_records_sql($sql);      
        
        return array($users, $course_users, $prefix_check);
    }
    
    /*
     * Get Users 
     * @param $courseid_str is string of course ids seperated by ','
     * @param $where_usr_str is selected uses
     */
    public function userGrades($courseid_str, $userid_str, $userids, $item_ids) {
        
        global $DB;        
        
        $sql = "SELECT g . *,gi.scaleid,gi.courseid,gi.id AS itemid,gi.itemtype
                FROM {grade_items} gi, {grade_grades} g
                WHERE g.itemid = gi.id AND gi.id IN (".implode(',',$item_ids).")
                AND gi.courseid IN(". $courseid_str .")
                AND g.userid
                IN (". $userid_str .")";

        $grades = $DB->get_records_sql($sql);
        $items = array();

        foreach($grades as $graderec) {
            if(in_array($graderec->userid, $userids)) { 
                if(!empty($graderec->scaleid)) {
                    
                    if($graderec->finalgrade > 0) {
                        $grade = $scales[$graderec->scaleid][(int)$graderec->finalgrade];
                    }else {
                        $grade = $graderec->finalgrade;
                    }
                    
                }else {
                    $grade = $graderec->finalgrade;
                }
                
                $items[$graderec->courseid][$graderec->userid][$graderec->itemid] = array('grade' => $grade);
            }
        }//echo '<pre>'; print_R($items); echo '</pre>';
        return $items;
    }
    /*
     * Get User other profile fields
     * @param $userid_str is string of user ids seperated by ','
     */
    public function getUserProfileFields($userid_str) {
        
        global $DB;

        $usr_info_data_arr = array();
        $sql = "SELECT id,name FROM {user_info_category} ORDER BY sortorder ASC";
        $usr_info_cat = $DB->get_records_sql($sql);

        $sql = "SELECT id,name,categoryid FROM {user_info_field}";
        $usr_info_field = $DB->get_records_sql($sql);

        if($usr_info_field) {
           foreach($usr_info_field as $field) {
               $usr_info_field_arr[$field->categoryid][] = array('id'   => $field->id,
                                                                 'name' => $field->name);
           }      
        }
        if(!empty($userid_str)) {
        
        $sql = "SELECT * FROM {user_info_data} WHERE userid IN ( $userid_str )";  
        $usr_info_data = $DB->get_records_sql($sql);
        if($usr_info_data) {
            foreach($usr_info_data as $info_data) {
                $usr_info_data_arr[$info_data->userid][$info_data->fieldid] = $info_data->data;
            }
        }
        }
        return array($usr_info_cat, $usr_info_field_arr, $usr_info_data_arr);
    }
    /*
     * Add Basic Columns to PHPExcel Object
     * @param $objPHPExcel ia PHPExcel Object
     * @param $num_of_users is number of users
     */
    public function getbasicColumnsExcel($objPHPExcel, $num_of_users, $row_pos, $sheetindex, $prefix_check = false) {
        
        $styleArray = array('borders' => array(
                            'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                     ),
               ),
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$row_pos, 'First Name')
                                 ->setCellValue('B'.$row_pos, 'Last Name')
                                 ->setCellValue('C'.$row_pos, 'ID Number')
                                 ->setCellValue('D'.$row_pos, 'Email');
        
        if($prefix_check) {
            $objPHPExcel->getSheet(0)->setCellValue('E'.$row_pos, 'Prefix');
        }

        $objPHPExcel->getDefaultStyle()->getFont()
                    ->setName('Arila')
                    ->setSize(10);

        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);            

        $objPHPExcel->getActiveSheet()->mergeCells('A'.$row_pos.':A'.($row_pos+1));
        $objPHPExcel->getActiveSheet()->mergeCells('B'.$row_pos.':B'.($row_pos+1));
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$row_pos.':C'.($row_pos+1));
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$row_pos.':D'.($row_pos+1));
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row_pos.':D'.$row_pos)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A'.($row_pos+1).':D'.($row_pos+1))->applyFromArray(array('borders' => array(
                                                                                'bottom' => array(
                                                                                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                                                                                     ),
                                                                               ),
                                                                        ));

        if($prefix_check) {
            $objPHPExcel->getActiveSheet()->getStyle('E'.($row_pos-1).':E'.($num_of_users+3))->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->mergeCells('E'.$row_pos.':E'.($row_pos+1));
            $objPHPExcel->getActiveSheet()->getStyle('E'.$row_pos)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }else {
            $objPHPExcel->getActiveSheet()->getStyle('D'.($row_pos-1).':D'.($num_of_users+3))->applyFromArray($styleArray);
        } 
        return $objPHPExcel;
    }
    /*
     * Add Other user Profile  Columns to PHPExcel Object
     * @param $objPHPExcel ia PHPExcel Object     
     */
    public function getUserProfileFieldsExcel($objPHPExcel, $usr_info_cat, $c_pos, $last_pos, $usr_info_field_arr, $num_of_users, $row_pos) {
        
        $field_id_arr = array();
        $styleArray = array('borders' => array(
                            'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,                            
                     ),
               ),
        );
        
        foreach($usr_info_cat as $inf_cat) {          
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c_pos,($row_pos-1), $inf_cat->name);

            if(isset($usr_info_field_arr[$inf_cat->id])) {
                $last_pos += count($usr_info_field_arr[$inf_cat->id]);

                if(count($usr_info_field_arr[$inf_cat->id]) > 1) {
                    $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($c_pos).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($c_pos+count($usr_info_field_arr[$inf_cat->id])-1).($row_pos-1));                
                }

                $field_inc = $c_pos;
                foreach($usr_info_field_arr[$inf_cat->id] as $field) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($field_inc, $row_pos, $field['name']);
                    $field_id_arr[] = array('id' => $field['id'], 'c_pos' => $field_inc);
                    $field_inc++;
                }
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($c_pos+count($usr_info_field_arr[$inf_cat->id])-1).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($c_pos+count($usr_info_field_arr[$inf_cat->id])-1).($num_of_users+3))->applyFromArray($styleArray);

                $c_pos += count($usr_info_field_arr[$inf_cat->id]) ;

            }else {
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($c_pos).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($c_pos+count($usr_info_field_arr[$inf_cat->id])-1).($num_of_users+3))->applyFromArray($styleArray);
                $c_pos ++;
            }

        }
        
        return array($objPHPExcel, $last_pos, $field_id_arr);
    }
    /*
     * Get Courses Excel
     */
    public function getCoursesExcel($objPHPExcel, $courses, $course_items, $last_pos, $num_of_users, $row_pos) {
        
        global $CFG;
        
        $cc_inc = $ic_inc = $last_pos; $all_courses_total = 0;
        $ci_cindex = array();
        $styleArray = array('borders' => array(
                            'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,                            
                     ),
               ),
        );
        foreach($course_items as $courseid=>$course_item) {
            
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($cc_inc,($row_pos-1), $courses[$courseid]->shortname);
            $start_point = $cc_inc;$num_items = 0;

            foreach($course_item as $itemrec) {
                $cc_inc++;
                $ci_cindex[$courseid][$itemrec->itemid] = $ic_inc;
                if($itemrec->itemname == '') {
                    $itemrec->itemname = 'Course Total';
                    $all_courses_total = $all_courses_total + $itemrec->grademax;
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc).$row_pos)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($ic_inc).($num_of_users+3))->applyFromArray($styleArray);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc-1).$row_pos.':'.PHPExcel_Cell::stringFromColumnIndex($ic_inc-1).($num_of_users+3))->applyFromArray($styleArray);
                }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,$row_pos, $itemrec->itemname);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,($row_pos+1), $itemrec->grademax);
                
                $ic_inc++;
                $num_items++;
            }
            
            if($num_items > 0) {
                $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($start_point).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).($row_pos-1));

                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($start_point).($row_pos-1).':'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).($row_pos-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($start_point).($row_pos+1).':'.PHPExcel_Cell::stringFromColumnIndex($start_point+$num_items-1).($row_pos+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            
        }
        return array($objPHPExcel, $ci_cindex, $ic_inc, $all_courses_total);
    }
    /*
     * Add Other columns like CGPA,Total etc to PHPExcel Object.
     */
    public function getOtherColumnsExcel($objPHPExcel, $ic_inc, $num_of_users,$all_courses_total) {
        
        $styleArray = array('borders' => array(
                            'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,                            
                     ),
               ),
        );
        
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,2, 'Course Total');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,3, $all_courses_total);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+1),2, '%');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+2),2, 'CGPA');
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc).'3:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc+1).'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'2:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).($num_of_users+3))->applyFromArray($styleArray);

        $objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'1')->getFill()->applyFromArray(
                array(
                        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array('rgb' => 'FFAE4D'),
                        'endcolor'   => array('rgb' => 'FFAE4D')
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'3')->getFont()->setBold(true);
        unset($styleArray);
        $styleArray = array(
               'borders' => array(
                     'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            
                     ),
               ),
        );

        $objPHPExcel->getActiveSheet()->getStyle('A'.($num_of_users+3).':'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).($num_of_users+3))->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('E3:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'3')->applyFromArray($styleArray);
        unset($styleArray);
        $styleArray = array(
               'borders' => array(
                     'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                           
                     ),
               ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex($ic_inc+2).'1')->applyFromArray($styleArray);
        
        return $objPHPExcel;
    }
    /*
     * Add Users Grades To PHPExcel Object
     */
    public function getUsersGradesExcel($objPHPExcel, $users, $course_users, $course_items, $field_id_arr, $usr_info_data_arr, $grades, $ci_cindex, $ic_inc, $all_courses_total) {
        global $CFG;
        $r_inc = 4; $course_totals_arr = array();
        foreach ($course_users as $user)  {            
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$r_inc, $users[$user->id]->firstname);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$r_inc, $users[$user->id]->lastname);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$r_inc, $users[$user->id]->idnumber);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$r_inc, $users[$user->id]->email);
            if(isset($users[$user->id]->prefix)) {
               $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$r_inc, $users[$user->id]->prefix);
            }

            if($CFG->report_users_show_user_profile_fields) {
                if(count($field_id_arr) > 0) {
                    foreach($field_id_arr as $field_data) {
                        if(isset($usr_info_data_arr[$user->id][$field_data['id']]))
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($field_data['c_pos'],$r_inc, $usr_info_data_arr[$user->id][$field_data['id']]);
                    }
                }
                
            }
            $course_total = '';
            foreach($course_items as $courseid => $items) {
                foreach($items as $item) {
                    if(isset($ci_cindex[$courseid][$item->itemid]) && isset($grades[$courseid][$user->id][$item->itemid]['grade'])) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ci_cindex[$courseid][$item->itemid],$r_inc, round($grades[$courseid][$user->id][$item->itemid]['grade'],2));                        
                    }
                    if($item->itemname == 'Course Total') {
                        $course_total = $course_total+round($grades[$courseid][$user->id][$item->itemid]['grade'],2);
                    }
                }
            }
            if(!empty($course_total)) {
                $percentage = '';
            }else {
                $percentage = round(($course_total/$all_courses_total)*100,2);
            }
            $course_totals_arr[] = $course_total;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc, $r_inc, $course_total);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+1), $r_inc, $percentage);
            /*if(isset($items[$user_data->id])) {
                foreach($items[$user_data->id] as $courseid => $user_items) {
                    $user_course_total = 0;
                    foreach($user_items as $key => $user_item) {
                        if(isset($ci_cindex[$courseid][$key])) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ci_cindex[$courseid][$key],$r_inc, round($user_item['grade'],2));
                            $user_course_total = $user_course_total + round($user_item['grade'],2);
                        }            
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ci_cindex[$courseid][0],$r_inc, $user_course_total);
                    $course_total = $objPHPExcel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($ci_cindex[$courseid][0]).'3')->getValue();
                    
                    if($course_total > 0) {
                        $user_percentage = 100 * ($user_course_total/$course_total);
                    }else {
                        $user_percentage = '';
                    }
                    
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ci_cindex[$courseid][-1],$r_inc, round($user_percentage).'%');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ci_cindex[$courseid][-1]).$r_inc)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $user_all_course_total = $user_all_course_total + $user_course_total;
                    $user_all_course_percentage = $user_all_course_percentage + round($user_percentage);
                    $all_perc_arr[] = round($user_percentage);

                }
            }
            if($max_marks < $user_all_course_total) {
                    $max_marks = $user_all_course_total;
            }
            $user_all_course_total_arr[] = $user_all_course_total;    
            //$avg_perc = round($user_all_course_percentage/count($all_perc_arr));
            $avg_perc = round(($user_all_course_total * 100)/$all_courses_total);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($ic_inc,$r_inc, $user_all_course_total);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($ic_inc+1),$r_inc, $avg_perc.'%');
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($ic_inc+1).$r_inc)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/
            $r_inc++;    
        }
        return array($objPHPExcel, $course_totals_arr);
    }
    public function getScale($scaleid) {
        global $DB;
        $sql = "SELECT id,scale FROM {scale} WHERE id = $scaleid ";
        
        $scales = $DB->get_records_sql($sql);                        
        
        return make_menu_from_list($scales[$scaleid]->scale);
    }
    public function get_num_users($courseid_str, $where_usr_str) {
        
        global $DB;
        $countsql ="SELECT COUNT(*)
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
                    AND u.deleted =0 ". $where_usr_str;
        
            $numusers = $DB->count_records_sql($countsql);
            
            return $numusers;
    }
}
?>