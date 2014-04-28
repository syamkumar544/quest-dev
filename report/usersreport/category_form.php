<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/lib/formslib.php');

class usersreport_category_form extends moodleform {
    // Define the form
    function definition() {
        
        $mform =& $this->_form;
        
        $category = $this->_customdata['category'];
        $sub_categories = $this->_customdata['sub_categories'];
        $selected_category = $this->_customdata['selected_category'];
        $ausers = $this->_customdata['uifilter']['ausers'];  
        $date_filter = $this->_customdata['date_filter'];
        $user_filter = $this->_customdata['user_filter'];
        $activity_filter = $this->_customdata['activity_filter'];
        $postdata = $this->_customdata['postdata'];
        if(!isset($postdata['category_id'])) $postdata['category_id'] = '';
        if(!isset($postdata['date_flag'])) {
            $postdata['date_flag'] = 0;
        }else {
            $postdata['date_flag'] = 1;
        }
       
        if(isset($postdata['startdate'])) {
            $startdate = $postdata['startdate']['day'].'-'.$postdata['startdate']['month'].'-'.$postdata['startdate']['year'];
            $startdate_tstmp = strtotime($startdate);
        }else {
            $startdate = date('d-m-Y');
            $startdate_tstmp = strtotime($startdate);
        }
        
        if(isset($postdata['enddate'])) {
            $enddate = $postdata['enddate']['day'].'-'.$postdata['enddate']['month'].'-'.$postdata['enddate']['year'];
            $enddate_tstmp = strtotime($enddate);
        }else {
            $enddate = date('d-m-Y');
            $enddate_tstmp = strtotime($enddate);
        }
             
        $mform->addElement('select', 'category_id', 'Choose Category', $category)->setSelected($selected_category);
        
        $html = "Sub Categories<br/> <div style='border: 1px solid #D9CEBB;
                                                 height: 100px;    
                                                 width: 250px;
                                                 overflow: auto;'>";
        if(!empty($selected_category) && $sub_categories) {
        foreach($sub_categories as $id => $category) {
          $html .= "<input type='checkbox' name=sub_cat[] value = '$id' checked = checked />";
          $html .= $category.'<br />';
        }
        }else {
          $html .= "<span style='color: #AA0000;'>No Batches in selected Category !</span>";
        }
        $html .= "</div>";
        $mform->addElement('html',$html);       
        
        if($date_filter) {
      
            //$mform->addElement('checkbox', 'date_flag', 'Use Date filter');
            if($postdata['date_flag']) {
                $mform->addElement('html','&nbsp;&nbsp;Use Date filter&nbsp;&nbsp;<input type="checkbox" name="date_flag" checked>');
            }else {
                $mform->addElement('html','&nbsp;&nbsp;Use Date filter&nbsp;&nbsp;<input type="checkbox" name="date_flag">');
            }
            
            $mform->addElement('date_selector', 'startdate', 'Start Date');
            $mform->setDefault('startdate', $startdate_tstmp);

            $mform->addElement('date_selector', 'enddate', 'End Date');
            $mform->setDefault('enddate', $enddate_tstmp);
            
        }
        if($activity_filter) {
            $activities = $this->get_activities();
            $select = $mform->addElement('select', 'activities', 'Activities', $activities, array('style' => 'width:200;height:200'));
            $select->setMultiple(true);                                                                               
            //$select->setSelected(array_keys($activities));

        }
        if($user_filter) {
            $html = '<table style="width:370px;">
                     <tr><td style="width:160px;">
                     <b>All Users:</b><br/>
                     <select multiple="multiple" id="lstBox1" style="height:200px;width:200px;">';
            foreach($this->_customdata['uifilter']['ausers'] as $id => $user) {
                $html .= '<option value="'. $id .'">'. $user .'</option>';
                if(isset($postdata['selected_users']) && count($postdata['selected_users']) > 0 && in_array($id,$postdata['selected_users'])) {
                    $selectedusers[$id] = $user;
                }
            }
            $html .= '</select></td>
                      <td style="width:50px;text-align:center;vertical-align:middle;">
                      <input type="button" id="btnRight" value ="Add"/>
                      <br/><input type="button" id="btnLeft" value ="Remove"/>
                      </td>
                      <td style="width:160px;">
                      <b>Selected Users: </b><br/>
                      <select multiple="multiple" id="lstBox2" name="selected_users[]" style="height:200px;width:200px;">';
           if(isset($selectedusers) && count($selectedusers) > 0) {
               foreach($selectedusers as $usrid => $name) {
                   $html .= '<option value="'. $usrid .'" selected>'. $name .'</option>';
               }
           }
            $html .= '</select>
                      </td>
                      </tr>
                      </table>';        
            $mform->addElement('html',$html);
        }
        $mform->setAttributes(array('action'=>'report.php','method' => 'POST'));
        $btnstring = 'Get Report';       

        $this->add_action_buttons(false, $btnstring); 
        
    }
    public function get_activities($plural = false) {
        global $DB,$CFG;
        $modnames = array(0 => array(), 1 => array());
        if ($allmods = $DB->get_records("modules")) {
          foreach ($allmods as $mod) {
              if (file_exists("$CFG->dirroot/mod/$mod->name/lib.php") && $mod->visible) {
                  $modnames[0][$mod->name] = get_string("modulename", "$mod->name");
                  $modnames[1][$mod->name] = get_string("modulenameplural", "$mod->name");
              }
          }
              collatorlib::asort($modnames[0]);
              collatorlib::asort($modnames[1]);
       }
      
      return $modnames[(int)$plural];
    
   }
}
