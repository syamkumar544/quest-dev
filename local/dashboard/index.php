<?php

require_once('../../config.php');
global $DB, $CFG;

require_once($CFG->libdir.'/adminlib.php');

require_login();

$PAGE->set_context(get_system_context());
$PAGE->set_pagelayout('general');
$PAGE->set_url(new moodle_url('/local/dashboard/index.php'));

$PAGE->navbar->add(get_string('dashboard'), new moodle_url('/local/dashboard/index.php'));

$PAGE->set_title(get_string('dashboard'));
$PAGE->set_heading(get_string('dashboard'));

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('dashboard'));
$PAGE->set_cacheable(false);
	if (!defined("SITEADMIN")) {
		define('SITEADMIN', 1);
	}
	if (!defined("L1MANAGER")) {
		define('L1MANAGER', 9);
	}
	if (!defined("L2MANAGER")) {
		define('L2MANAGER', 14);
	}
	if (!defined("CONTENTPROVIDER")) {
		define('CONTENTPROVIDER', 13);
	}
	if (!defined("TRAINER")) {
		define('TRAINER', 4);
	}
	if (!defined("LEARNER")) {
		define('LEARNER', 5);
	}

	$role_ids_query = $DB->get_records_sql('SELECT roleid FROM {role_assignments} WHERE userid=:uid AND contextid=1', array('uid' => $USER->id));
	$role_ids = array_keys($role_ids_query);

	$content = "";
	if(in_array(SITEADMIN, $role_ids)) {
			$manage_users_link = array(
															'userlist' => $CFG->wwwroot.'/groups/people-directory',
															'userbulk' => $CFG->wwwroot.'/admin/user/user_bulk.php',
															'addnewuser' => $CFG->wwwroot.'/groups/admin/people/create',
															'uploadusers' => $CFG->wwwroot.'/groups/admin/people/user_import/add',
															/*'Define roles' => $CFG->wwwroot.'/admin/roles/manage.php',
															'Assign system roles' => $CFG->wwwroot.'/admin/roles/assign.php?contextid=1'*/
														);
			$manage_hierarchies_link = array('positions' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=position',
																			 'organization' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=organisation', 
																			 'competencyplural' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=competency', 
																			 'goalplural' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=goal');
			$manage_courses_link = array(
																	 'createcategory' => $CFG->wwwroot.'/course/editcategory.php?parent=0',
																	 'createcourse' => $CFG->wwwroot.'/course/edit.php?category=3&returnto=topcatmanage',
																	 'createprogram' => $CFG->wwwroot.'/totara/program/add.php?category=3',
																	 'createcertification' => $CFG->wwwroot.'/totara/program/add.php?category=3&iscertif=1',
																	 'createtraining' => $CFG->wwwroot.'/groups/node/add/panopoly-event',
																	 'createcommunity' => $CFG->wwwroot.'/groups/node/add/group',
			                             'managecourses' => $CFG->wwwroot.'/course/manage.php',
																	 'manageprograms' => $CFG->wwwroot.'/totara/program/manage.php',
																	 'managecertifications' => $CFG->wwwroot.'/totara/program/manage.php?viewtype=certification',
																	 'customfields' => $CFG->wwwroot.'/totara/customfield/index.php?prefix=course',
																	);
			$manage_backup_link = array('generalbackdefaults' => $CFG->wwwroot.'/admin/settings.php?section=backupgeneralsettings',
																	'importgeneralsettings' => $CFG->wwwroot.'/admin/settings.php?section=importgeneralsettings',
																	'automatedsetup' => $CFG->wwwroot.'/admin/settings.php?section=automated'
																	);
			$manage_reports_link = array('reportsbuilder' => $CFG->wwwroot.'/totara/reportbuilder/index.php',
																	 'logs' => $CFG->wwwroot.'/report/log/index.php?id=1',
																	 'livelogs' => $CFG->wwwroot.'/report/loglive/index.php',
																	 'samplereports' => $CFG->wwwroot.'/groups/reports'
																	);
			$links_array = array(
											'manage users' => $manage_users_link,
											'manage hierarchies' => $manage_hierarchies_link,
											'manage courses' => $manage_courses_link,
											'manage backup' => $manage_backup_link,
											'manage reports' => $manage_reports_link
										);
			$links_title_array = array('manage users' => 'manageusers', 'manage hierarchies' => 'managehierarchies', 'manage courses' => 'managecourses', 
																	'manage backup' => 'managebackup', 'manage reports' => 'managereports');
	}
	else if(in_array(L1MANAGER, $role_ids) || in_array(L2MANAGER, $role_ids)) {
			$manage_hierarchies_link = array();
			$manage_users_link = array();
			
			if(in_array(L1MANAGER, $role_ids)) {
				$manage_users_link = array(
															'userlist' => $CFG->wwwroot.'/groups/people-directory'
														);	
				$manage_hierarchies_link = array('positions' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=position',
																			 'organization' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=organisation', 
																			 'competencyplural' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=competency', 
																			 'goalplural' => $CFG->wwwroot.'/totara/hierarchy/framework/index.php?prefix=goal');
				$manage_l1_courses_link = array(
																	 'createcategory' => $CFG->wwwroot.'/course/editcategory.php?parent=0');
			}
			
			$manage_l1_l2courses_link = array(
																	 'createcourse' => $CFG->wwwroot.'/course/edit.php?category=3&returnto=topcatmanage',
																	 'createprogram' => $CFG->wwwroot.'/totara/program/add.php?category=3',
																	 'createcertification' => $CFG->wwwroot.'/totara/program/add.php?category=3&iscertif=1',
																	 'createtraining' => $CFG->wwwroot.'/groups/node/add/panopoly-event',
																	 'createcommunity' => $CFG->wwwroot.'/groups/node/add/group',
			                             'managecourses' => $CFG->wwwroot.'/course/manage.php',
																	 'manageprograms' => $CFG->wwwroot.'/totara/program/manage.php',
																	 'managecertifications' => $CFG->wwwroot.'/totara/program/manage.php?viewtype=certification',
																	);
			$manage_reports_link = array();
			if(in_array(L1MANAGER, $role_ids)) {														
				$manage_courses_link = array_merge($manage_l1_courses_link, $manage_l1_l2courses_link);
			}
			else {
				$manage_courses_link = $manage_l1_l2courses_link;
			}
			$manage_reports_link = array('reportsbuilder' => $CFG->wwwroot.'/totara/reportbuilder/index.php',
																	 'logs' => $CFG->wwwroot.'/report/log/index.php?id=1',
																	 'livelogs' => $CFG->wwwroot.'/report/loglive/index.php',
																	 'samplereports' => $CFG->wwwroot.'/groups/reports'
																	);
			
			$links_array = array(
											'manage users' => $manage_users_link,
											'manage hierarchies' => $manage_hierarchies_link,
											'manage courses' => $manage_courses_link,
											'manage reports' => $manage_reports_link
										);
								
			$links_title_array = array('manage users' => 'manageusers', 'manage hierarchies' => 'managehierarchies', 'manage courses' => 'managecourses', 'manage reports' => 'managereports');
	}
	else if(in_array(CONTENTPROVIDER, $role_ids)) {
			$manage_courses_link = array(
																	 'createcategory' => $CFG->wwwroot.'/course/editcategory.php?parent=0',
																	 'createcourse' => $CFG->wwwroot.'/course/edit.php?category=3&returnto=topcatmanage',
																	 'createprogram' => $CFG->wwwroot.'/totara/program/add.php?category=3',
																	 'createcertification' => $CFG->wwwroot.'/totara/program/add.php?category=3&iscertif=1',
																	 'createtraining' => $CFG->wwwroot.'/groups/node/add/panopoly-event',
																	 'createcompetency' => $CFG->wwwroot . '/totara/hierarchy/framework/edit.php?prefix=competency',
																	 'managecompetency' => $CFG->wwwroot . '/totara/hierarchy/framework/index.php?prefix=competency',
			                             'managecourses' => $CFG->wwwroot.'/course/manage.php',
																	 'manageprograms' => $CFG->wwwroot.'/totara/program/manage.php',
																	 'managecertifications' => $CFG->wwwroot.'/totara/program/manage.php?viewtype=certification'
																	);
			
			$links_array = array(
											'manage courses' => $manage_courses_link,
										);
			$links_title_array = array('manage courses' => 'managecourses');
	}
	else if(in_array(TRAINER, $role_ids)) {
			$content = "";
			$content .= "<ul><li>TRAINER Dashboard</li></ul>";
	}
	else if(in_array(LEARNER, $role_ids)) {
			
	}
	if(!empty($links_title_array)) {
		foreach($links_title_array as $links_key => $links_title) {
			if(!empty($links_array[$links_key])) {
				$content .= "<div class='span4 dashboard-".str_replace(' ', '-', $links_key)."'><div class='dashboard-menu-title'>".get_string($links_title)."</div>";
				$content .= "<ul class='dash-menu-list'>";
				foreach($links_array[$links_key] as $link_title => $link) {
					$content .= "<li><a href='$link'>".get_string($link_title)."</a></li>";
				}
				$content .= "</ul></div>";
			}
		}
	}
	echo $content;
echo $OUTPUT->footer();
