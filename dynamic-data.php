<?php

/**
 * Plugin Name: Dynamic Data Input
 * Description: For creating dynamic inputs for contest collections, counting programs and more.
 * Author:      John Overall / Shawn DeWolfe
 * Author URI:  https://JohnOverall.com
 * Plugin URI:  https://WPPluginsAtoZ.com/dynamic-data
 * Text Domain: dynamic-data
 * Domain Path: /languages/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.2
 *
 * 
 **/

/*
* Copyright (C)  2019-2022 John Overall
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

/** Notes to deal with
if tables currently do not have an id and primary key to ad id to tables
ALTER TABLE `tablename` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY

 
The Short Codes
[region_form]
[region_show_count]
[display_municipality_count]
[school_form]
[school_count]
[display_school_count]
/**

*/

global $wpdb;
define("FC_COUNT", $wpdb->prefix . "fc_count");
define("FC_MUNICIPALITIES", $wpdb->prefix . "fc_municipalities");
define("FC_SCHOOLS", $wpdb->prefix . "fc_schools");
define("FC_SCHOOLS_COUNT", $wpdb->prefix . "fc_schools_count");

register_activation_hook( __FILE__, 'flowers_install' );

// adding the code to create new tables here
function flowers_install () {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$table_name = FC_COUNT; 
	$sql = "CREATE TABLE $table_name (
		`ccount_id` int(11) NOT NULL,
		`municipality` tinyint(4) NOT NULL DEFAULT '0',
		`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`ip` varchar(15) NOT NULL DEFAULT '',
		`count` bigint(32) NOT NULL DEFAULT '0'
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name ADD PRIMARY KEY (`ccount_id`);";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name MODIFY `ccount_id` int(11) NOT NULL AUTO_INCREMENT;";
	dbDelta( $sql );

	$table_name = FC_MUNICIPALITIES; 
	$sql = "CREATE TABLE $table_name (
		`id` tinyint(4) NOT NULL DEFAULT '0',
		`name` varchar(40) NOT NULL DEFAULT ''
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name ADD PRIMARY KEY (`id`);";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
	dbDelta( $sql );   

	$table_name = FC_SCHOOLS; 
	$sql = "CREATE TABLE $table_name (
		`id` tinyint(4) NOT NULL DEFAULT '0',
		`name` varchar(150) NOT NULL DEFAULT '',
		`municipalities_id` tinyint(4) NOT NULL DEFAULT '0'
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name ADD PRIMARY KEY (`id`);";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
	dbDelta( $sql );   

   $table_name = FC_SCHOOLS_COUNT; 
	$sql = "CREATE TABLE $table_name (
		`scount_id` int(11) NOT NULL,
		`school` varchar(150) NOT NULL DEFAULT '0',
		`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`ip` varchar(15) NOT NULL DEFAULT '',
		`count` bigint(32) NOT NULL DEFAULT '0',
		`teacher` varchar(150) NOT NULL DEFAULT ''
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name ADD PRIMARY KEY (`scount_id`);";
	dbDelta( $sql );

	$sql = "ALTER TABLE $table_name MODIFY `scount_id` int(11) NOT NULL AUTO_INCREMENT;";
	dbDelta( $sql );
}

/** start active code **/
/** start active code **/
//custom shortcodes

add_shortcode( 'region_form', 'flowers_form_count' );
add_shortcode( 'region_show_count', 'flowers_show_count' );
add_shortcode( 'display_municipality_count', 'flowers_municipalities_display' );
add_shortcode( 'display_school_count', 'flowers_schools_display' );
add_shortcode( 'school_form', 'flowers_form_school' );
add_shortcode( 'school_count', 'flowers_show_school' );


// add_filter('widget_text', 'do_shortcode');

////// Flower Count Display Muncipalities totals

function flowers_municipalities_display() {
	$output = '<table style="width:100%" border="1">';
	$output .= '<tr>';
	$output .= '<th>Municipality</th>';
	$output .= '<th colspan="1">Total</th>';
	$output .= '<th colspan="1">Total</th>';	
	$output .= '</tr>';
	
  
    global $wpdb;
    $result = $wpdb->get_results ( "SELECT mn.name as name, SUM(fc.`count`) as `the_count` FROM ".FC_MUNICIPALITIES." mn INNER JOIN ".FC_COUNT." fc ON fc.`municipality` = mn.id GROUP BY fc.`municipality` ORDER BY `the_count` DESC", ARRAY_A);
    foreach ( $result as $print ) {
		$human_count = $print['the_count'];
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000000000)) {
			$human_count = strval(round(intval($print['the_count']) / 10000000) / 100)." billion";
		}
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000000)) {
			$human_count = strval(round(intval($print['the_count']) / 10000) / 100)." million";
		}
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000)) {
			$human_count = strval(number_format($print['the_count']));
		}

    	$output .= '<tr>';
		$output .= '<td>'.$print['name'].'</td>';
		$output .= '<td>'.number_format($print['the_count']).'</td>';
		$output .= '<td>'.$human_count.'</td>';

		$output .= '</tr>';
    }
	$output .= '</table>';
	
	return $output;
}

////// Flower Count Display School totals

function flowers_schools_display() {
	$output = '<table style="width:100%" border="1">';
	$output .= '<tr>';
	$output .= '<th>School</th>';
	$output .= '<th colspan="1">Total</th>';
	$output .= '<th colspan="1">Total</th>';	
	$output .= '</tr>';
  
    global $wpdb;
    $result = $wpdb->get_results ( "SELECT sc.name as name, SUM(fsc.`count`) as `the_count` FROM ".FC_SCHOOLS." sc INNER JOIN ".FC_SCHOOLS_COUNT." fsc ON fsc.`school` = sc.`name` GROUP BY fsc.`school` ORDER BY `the_count` DESC", ARRAY_A);
    foreach ( $result as $print ) {
		$human_count = $print['the_count'];
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000000000)) {
			$human_count = strval(round(intval($print['the_count']) / 10000000) / 100)." billion";
		}
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000000)) {
			$human_count = strval(round(intval($print['the_count']) / 10000) / 100)." million";
		}
		if (($human_count == $print['the_count']) && ($print['the_count'] > 1000)) {
			$human_count = strval(number_format($print['the_count']));
		}

    	$output .= '<tr>';
		$output .= '<td>'.$print['name'].'</td>';
		$output .= '<td>'.number_format($print['the_count']).'</td>';
		$output .= '<td>'.$human_count.'</td>';

		$output .= '</tr>';
    }
	$output .= '</table>';
	
	return $output;
}
	
/////FLOWER COUNT Region Submission Form/////////////

function flowers_form_count() {
	global $wpdb;

	$submitted = FALSE;
	if (isset($_POST['submit_cn'])) {
		if (strlen($_POST['submit_cn']) > 0) {		
			$submitted = TRUE;
		}
	}

	if ($submitted == TRUE) {
		$error_string = "<font color=white><strong>";
		$count = preg_replace( '/[^0-9]/', '', $_POST['count'] );
		if ($count != 0) {
			$name = "";
			$note = "";

			if (isset($_POST['name'])) {
				$name = $_POST['name'];
			}
			if (isset($_POST['note'])) {
				$note = $_POST['note'];
			}
			if (isset($_POST['municipality'])) {
				$municipality = $_POST['municipality'];
			}

			$ip = $_SERVER['REMOTE_ADDR'];
		
		  $municipalities = $wpdb->get_row( 
			$wpdb->prepare("SELECT id, name FROM ".FC_MUNICIPALITIES." WHERE id = '%d' LIMIT 1", $municipality), ARRAY_A 
		  );

		  $wpdb->insert( FC_COUNT, array( 
			'municipality' => $municipalities['id'],
			'ip' => $ip,
			'count' => $count 
		  ));
		  flower_cookie(); // set a cookie

		  // Build String:
		  $outputStr = "Thank you for submitting your report of  ";
		  if ($count == 1) {
			$outputStr = $outputStr . "one flower discovered in " . $municipalities['name'] . ".";
		  } else {
			$outputStr = $outputStr . number_format($count) . " flowers counted in " . $municipalities['name'] . ".";
		  }

		  // Output to Screen:
		  $error_string = $error_string . $outputStr;
		  $output = '<h3>' . $outputStr . '</h3>';

    } else {
      $error_string = $error_string . "You entered ZERO.  Go back outside and look harder!";
    }
    $error_string = $error_string . "</strong></font>";
	} else {
		global $wp;
		$uri = home_url( $wp->request );

        $flood = false;
        if(isset($_COOKIE['flower_count'])) {
           if ($_COOKIE['flower_count'] == 'true') {
               $flood = true;
           } 
        }

        if ($flood === false) {
    		$output = '
    		<h1>Submit Your Flower Count</h1>
    			<form method="POST" action="'.$uri.'">
    			  <p>Your Municipality:</p>
    				  <select name="municipality" class="form_fields" style="width: 200px;">
    			';
    
    		$municipalities = $wpdb->get_results("SELECT * FROM ".FC_MUNICIPALITIES, ARRAY_A 
    		);
    
    		foreach($municipalities as $municipality) {
    		  $output .= "<option value='" . $municipality['id'] . "'>" . $municipality['name'] . "</option>";
    		}
    	 	$output .= '
    				</select>
    				  <p>Your Count:</p>
    				 <input name="count" type="number" class="form_fields" id="textfield" min="1" max="999999999" size="10" maxlength="9"  style="width: 200px;" />
    				  <input name="submit_cn" type="submit" width="5" class="form_fields" id="button" value="Submit" />
    			</form>
    		';
        }
        else {
            $output = 'Thank You for your submission Stand by to submit another flower count you will be able to submit again shortly.';
        }
	}
	return $output;
}	


/////FLOWER COUNT School Submission Form/////////////

function flowers_form_school() {
	global $wpdb;
	$submitted = FALSE;
	if (isset($_POST['submit_sc'])) {
		if (strlen($_POST['submit_sc']) > 0) {		
			$submitted = TRUE;
		}
	}

	if ($submitted == TRUE) {
		$error_string = "<font color=white><strong>";
		$count = preg_replace( '/[^0-9]/', '', $_POST['count'] );
		if ($count != 0) {
			$school = "";
			$teacher = "";

			if (isset($_POST['school'])) {
				$school = $_POST['school'];
			}
			if (isset($_POST['teacher'])) {
				$teacher = $_POST['teacher'];
			}

		  $ip = $_SERVER['REMOTE_ADDR'];

		  $municipality = $wpdb->get_row( 
			$wpdb->prepare("SELECT * FROM ".FC_SCHOOLS." WHERE name = '%s' LIMIT 1", $school), ARRAY_A 
		  );
			
		  // Add to Database:

		  $wpdb->insert( FC_COUNT, array( 
			'municipality' => $municipality['municipalities_id'],
			'ip' => $ip,
			'count' => $count 
		  )); 

		  $wpdb->insert( FC_SCHOOLS_COUNT, array( 
			'school' => $school,
			'ip' => $ip,
			'count' => $count,
			'teacher' => $teacher,
		  )); 	
		  flower_cookie(); // set a cookie

		  // Build String:
		  $outputStr = "<h3>Thank you for submitting your school's Flower Count</h3>";
		  if ($count == 1) {
			$outputStr = $outputStr . "We have recorded one flower discovered by the students of " . $school . " Elementary School with the teacher of" . $teacher;
		  } else {
			$outputStr = $outputStr . "We have recorded  <strong>" . number_format($count) . "</strong> flowers, discovered by the students of " . $school . " Elementary School submitted by  " . $teacher . ".";
		  }

		  // Output to Screen:
		  $error_string = $error_string . $outputStr;
		  $output = '<h3>' . $outputStr . '</h3>';
    } else {
      $error_string = $error_string . "You entered ZERO.  Go back outside and look harder!";
    }
    $error_string = $error_string . "</strong></font>";
	} else {
		global $wp;
		$uri = home_url( $wp->request );

        $flood = false;
        if(isset($_COOKIE['flower_count'])) {
           if ($_COOKIE['flower_count'] == 'true') {
               $flood = true;
           } 
        }

        if ($flood === false) {
    		$output = '
    		<div class="schlsubmit">
    			<h2>Submit Your School\'s Flower Count</h2>
    			<form method="POST" action="'.$uri.'">
    			  <p>Your School:</p>
    				  <select name="school" class="form_fields">
    			';
    		
    		$schools = $wpdb->get_results( "SELECT sc.name AS scname, mn.name AS mnname FROM ".FC_SCHOOLS." sc INNER JOIN ".FC_MUNICIPALITIES." mn ON mn.id = sc.`municipalities_id` ORDER BY mn.name, sc.name", ARRAY_A );
    
    		$optgroup = "";
    		foreach($schools as $school) {
    			if ($school['mnname'] != $optgroup) {
    				if ($optgroup != "") {
    					$output .= '</optgroup>';	
    				}
    				$output .= '<optgroup label="'.$school['mnname'].'">';	
    				$optgroup = $school['mnname'];
    			}
    
    			$output .= "<option value='" . $school['scname'] . "'>" . $school['scname'] . "</option>";
    		}
    		$output .= '</optgroup>';
    		$output .= '
    			</select>
    				 <br/>
    				  <p>Submitting Teacher:</p>
    				 <input name="teacher" type="text" class="form_fields" id="textfield" size="16" maxlength="25" />
    				 <br/>
    				  <p>Your Count:</p>
    				 <input name="count" type="text" class="form_fields" id="textfield" size="16" maxlength="23" />
    			 <br/>
    				  <input name="submit_sc" type="submit" width="5" class="form_fields" id="button" value="Submit" />
    			</form></div>
    		';
    	}
        else {
            $output = 'Thank You for your submission Stand by to submit another flower count you will be able to submit again shortly.';
        }		
	}
	return $output;
}	

/////FLOWER COUNT Display Region Count/////////////

function flowers_show_count() {
	global $wpdb;

	$num = $wpdb->get_row("SELECT SUM(count) AS the_count FROM ".FC_COUNT, ARRAY_A);
    $count = $num['the_count'];
	return number_format($count, 0);
}

/////FLOWER COUNT Display Schools Count/////////////

function flowers_show_school() {
	global $wpdb;

	$num = $wpdb->get_row("SELECT SUM(count) AS the_count FROM ".FC_SCHOOLS_COUNT, ARRAY_A);
    $count = $num['the_count'];
	return number_format($count, 0);
}


function flower_cookie() { 
    $submitted = 'true';
    if(!isset($_COOKIE['flower_count'])) {
        setcookie('flower_count', $submitted, time()+600); // 10 min.
    }
} 