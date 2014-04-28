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
 * Settings for the backups report
 *
 * @package    report
 * @subpackage backups
 * @copyright  2007 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('usersreport', get_string('usersreport', 'report_usersreport'), "$CFG->wwwroot/report/usersreport/category.php",'moodle/backup:backupcourse'));

if ($ADMIN->fulltree) {
    // report settings
    $settings->add(new admin_setting_configcheckbox('report_users_enable_date_filter', 'Enable Date Filter',
                           'Enable "Date Filter" to generate report between dates of course started', 0));

    $settings->add(new admin_setting_configcheckbox('report_users_enable_users_filter', 'Enable User Filter',
                           'Enable "User Filter" to generate report of selected users', 0));

    $settings->add(new admin_setting_configcheckbox('report_users_enable_activity_filter', 'Enable Activity Filter',
                           'Enable "Activity Filter" to generate report of selected activities', 0));

    $settings->add(new admin_setting_configcheckbox('report_users_show_user_profile_fields', 'Show Profile Fields',
                           'Show "User Profile Fields" in report', 0));

   
}