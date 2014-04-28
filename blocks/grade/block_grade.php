<?php
class block_grade extends block_base {
    public function init() {
        $this->title = get_string('grade', 'block_grade');
    }

    public function get_content() {
				global $CFG, $USER, $DB;
        if ($this->content !== null) {
              return $this->content;
        }
        $text = $export_text = '';
        $mycourses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC');
        $is_student = 0;
        $student_role = $DB->get_record('role', array('shortname' => 'facilitator'));
				
        if($mycourses) {
            $text .= "<p>View Grades</p>";
            $export_text .= "<p>Export Grades</p>";
            $text .= "<ul>";
            $export_text .= "<ul>";
            foreach ($mycourses as $course) {
						 $context = get_context_instance(CONTEXT_COURSE, $course->id);
               if($studentrecord = $DB->get_record('role_assignments', array('contextid' => $context->id, 'userid' => $USER->id, 'roleid' => $student_role->id))) {
                     $is_student = 1;         
               }
               $text .= "<li>";
               $text .= "<a href='".$CFG->wwwroot."/grade/report/grader/index.php?id=".$course->id."'>".$course->fullname."</a>";
               $text .= "</li>";
               $export_text .= "<li>";
               $export_text .= "<a href='".$CFG->wwwroot."/grade/export/txt/index.php?id=".$course->id."'>".$course->fullname."</a>";
               $export_text .= "</li>";
            }
            $text .= "</ul>";
            $export_text .= "</ul>";
            $text .= $export_text;
        }else {
            $text = "No Courses !";
        }
				if($is_student != 1) {
            $text = '';
        }
        $this->content         =  new stdClass();
        $this->content->text   = $text;
        //$this->content->footer = 'Footer here...';
  
        return $this->content;
    }
}
