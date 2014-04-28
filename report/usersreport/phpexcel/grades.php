<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2012 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.8, 2012-10-12
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

$dbhost= "localhost"; //your MySQL Server
$dbuser = "root"; //your MySQL User Name
$dbpass = ""; //your MySQL Password
$dbname = "spjimr";

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
	
	

/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';
require('../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/excellib.class.php');
global $DB,  $CFG, $Connect;
	$Connect = mysql_connect($dbhost, $dbuser, $dbpass)
	or die("Couldn't connect to MySQL:<br>" . mysql_error() . "<br>" . mysql_errno());
	
	mysql_select_db($dbname, $Connect);
	
	///print the course & course activities 
	$sql = 'select gi.itemname,c.fullname from mdl_course c 
	join mdl_course_categories cc on cc.id = c.category
	join mdl_grade_items gi on c.id=gi.courseid
	where  c.category = 5 ORDER BY c.fullname, gi.itemname ASC';

	$items = $DB->get_records_sql($sql);
		
	foreach($items as $item)
	{
		if($item->itemname)
		$g_item[$item->fullname][]= $item->itemname;
		
	}

	///prints course totals
	$grade_sql = "SELECT u.firstname AS 'First' , u.lastname AS 'Last', c.fullname AS 'Course', cc.name AS 'Category', CASE 
  WHEN gi.itemtype = 'course' 
   THEN c.fullname + ' Course Total'
  ELSE gi.itemname
END AS 'ItemName',
 
ROUND(gg.finalgrade,2) AS Grade
FROM mdl_course AS c
JOIN mdl_context AS ctx ON c.id = ctx.instanceid
JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
JOIN mdl_user AS u ON u.id = ra.userid
JOIN mdl_grade_grades AS gg ON gg.userid = u.id
JOIN mdl_grade_items AS gi ON gi.id = gg.itemid
JOIN mdl_course_categories AS cc ON cc.id = c.category
 
WHERE  cc.id =' 5' and  gi.courseid = c.id
ORDER BY u.firstname, c.fullname,gi.itemname, gg.finalgrade DESC";

	$grade_items = mysql_query($grade_sql,$Connect) or die("Couldn't execute query:<br>" . mysql_error(). "<br>" . mysql_errno());
	//echo $grade_items;

	//$rows = mysql_fetch_object($grade_items);
//echo '<pre>';print_r($rows);echo '</pre>';

// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

// Set document properties
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
								 ->setLastModifiedBy("Maarten Balliauw")
								 ->setTitle("Office 2007 XLSX Test Document")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");


// Add some data
	$objPHPExcel->setActiveSheetIndex(0)
    	        ->setCellValue('A4', 'First Name')
        	    ->setCellValue('B4', 'Surname!')
            	->setCellValue('C4', 'ID number')
	            ->setCellValue('D4', 'Email Address!');
			
			
//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$r, $sum_value);

	$c=4; $r=3; $rr=4;	
	foreach ($g_item as $key => $value)
	{
		$c_name = $key;
		//echo $c_name.'<br>';
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$r, $c_name);
	
		foreach($value as $data)
		{
			$g_name=$data;
			//echo $g_name.'<br>';
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$rr, $g_name);
			
			$item_name[] = $c.','.$g_name;
			$c++;
		}
		$item_name[] = $c.','.$g_name.','.$c_name;
		$c++;$c++;
		
		//$c=$c+count($value);
	}
//echo '<pre>';print_r($item_name_data);echo '</pre>';
	
	/*	$email = $grade_item->email;
		echo $email;
			*/
			$flag = 0;
			$c1=1;
			$rr1 = 6;
	while($row = mysql_fetch_object($grade_items))
	{
		$email=$row->email;
		$fname = $row->First;
		$lname = $row->Last;
		$fgrade = $row->Grade;
		$iname = $row->ItemName;
		//echo $iname.'-'.$fgrade.'<br>';
		$flag++;
		if ($flag == 100) {
		break;	
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c1,$rr1, $fname);
		$c1++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c1,$rr1, $lname);
		$c1++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c1,$rr1, $email);
		$c1++;
			//$item_grade[] = $email.','.$fname.','.$lname.','.$fgrade.','.$iname;
			foreach($item_name as $key1 => $value1)
	{
		$item_name_data = explode(',',$value1);	
					
		if($iname == $item_name_data[1] && isset($item_name_data[2]))
		{
			$c1 = $item_name_data[0];
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c1,$rr1, $fgrade);
			$c1++;
			//echo $c1.'hi<br>';
		}
		else 
		{
			//echo $item_name_data[1];
			$c1 = $item_name_data[0];
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c1,$rr1, $fgrade);
			$c1++;
			//echo $c1.'<br>';
		}
	}
		if($fname1==!$fname)
		{		
		$rr1++;
		}
		$fname1 = $fname;
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$rr, $iname);

		//print '<pre>';
		//print_r($item_grade);
	}
	//echo '<pre>';print_r($item_grade);echo '</pre>';

	
	
	
		


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Main Grades');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="01simple.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
