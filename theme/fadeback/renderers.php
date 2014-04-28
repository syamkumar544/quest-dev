<?php
class theme_fadeback_core_renderer extends core_renderer {

    public function heading($text, $level = 2, $classes = 'main', $id = null) {

 	if($level == 2) {
    $content  = html_writer::start_tag('div', array('class'=>'headingwrap'));
    $content .= parent::heading($text, $level, $classes, $id);
    }
   	else {
    $content  = parent::heading($text, $level, $classes, $id);
    }
    if($level == 2) {
    $content .= html_writer::end_tag('div');
    }
    return $content;
}
 

	protected function render_custom_menu(custom_menu $menu) {
        // Our code will go here shortly
        $mycourses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC');
	//$mycourses = $this->page->navigation->get('mycourses');
 
if (isloggedin() && $mycourses) {
    $branchlabel = get_string('grade');
    $branchurl   = new moodle_url('/course/index.php');
    $branchtitle = $branchlabel;
    $branchsort  = 10000;
 
    $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
 
    foreach ($mycourses as $coursenode) {
//print_r($coursenode);
        $branch->add($coursenode->shortname, new moodle_url('/grade/report/user/index.php?id=', array('id' =>  $coursenode->id)), $coursenode->shortname);
    }
}
 return parent::render_custom_menu($menu);
    }
        

    //end class
 
}
