<?php

/*
Plugin Name: DandyID Services
Plugin URI: http://solidcode.com/
Description: Retrieves <a href="http://dandyid.org">DandyID</a> services for the configured user and displays them in the sidebar. After activating this plugin, please visit the <a href="options-general.php?page=dandyid-services/dandyid-services.php">Settings -&gt; DandyID Services</a> page to configure the DandyID required settings.
Version: 1.0.2
Author: Neil Simon
Author URI: http://solidcode.com/
*/


/*
 Copyright 2009 DandyID.

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, 5th Floor, Boston, MA 02110 USA
*/


// Include the dandyid class
require_once 'class.dandyid.php';


// Constants
define ('DANDYID_CACHE_FILE',               './dandyid_cache.csv');
define ('DANDYID_WP_AUTOCOPIED_CACHE_FILE', '../dandyid_cache.csv');
define ('DANDYID_URL',                      'http://www.dandyid.org/');
define ('DANDYID_PROFILE_URL',              'http://www.dandyid.org/beta/users/user_id/');
define ('DANDYID_MINI',                     'http://www.dandyid.org/code/images/miscellaneous/favicons/dandyidmini.png');
define ('DANDYID_API_KEY',                  '17ps6defe5fnem02czzsv95771wu4qe5w5x3');
define ('DANDYID_API_TOKEN',                'hbhvfwjuitwvsvoo5suatq6xgj2cnye6av1p');
define ('DANDYID_API_URL',                  'http://www.dandyid.org/api/');
define ('DANDYID_WP_OPTIONS',               'dandyID_options');


function dandyIDServices_getTable ()
    {
    // Initialize output buffer (returned by this function)
    $buf = '';

    // Get the currentDate and the cacheFileDate
    $currentDate   = date ('Y-m-d');
    $cacheFileDate = date ('Y-m-d', @filemtime (DANDYID_CACHE_FILE));

    // If the dates do not match...
    if ($cacheFileDate != $currentDate)
        {
        // Refresh the cache file
        dandyIDServices_refreshCacheFile ();
        }

    // Load existing options from wp database/
    $dandyID_options = get_option (DANDYID_WP_OPTIONS);

    // Always populate the DandyID sidebar fields from the cache file -- NEVER from the API directly
    if (($hCacheFile = fopen (DANDYID_CACHE_FILE, 'r')) == FALSE)
        {
        $buf .= 'Warning: Unable to load DandyID Services. Please contact nsimon[at]solidcode dotcom for help.<br />';
        }
    else
        {
        // Display the DandyID-Mini chicklet, with link to the users profile
        $buf .= '<a href="' . DANDYID_PROFILE_URL . $dandyID_options ['user_id'] .
                '"><img src="' . DANDYID_MINI . '" /></a>&nbsp; &nbsp;';

        // Only use the <table> tag if (show_text_links == TRUE)
        if ($dandyID_options ['show_text_links'] == TRUE)
            {
            // BEGIN table: one line for EACH service, containing chicklet and text link to service
            $buf .= '<table border="0" cellspacing="4">';
            }

        // Add services
        while (($cacheCSVLine = fgetcsv ($hCacheFile)) != FALSE)
            {
            $cacheUrl        = $cacheCSVLine [0];
            $cacheSvcName    = $cacheCSVLine [1];
            $cacheSvcFavicon = $cacheCSVLine [2];

            if ($dandyID_options ['show_text_links'] == TRUE)
                {

                // Add table row with 2 columns: svcFavicon, svcName (each links to user service url)
                //   Column 1: Service Favicon
                $buf .= '<tr>';
                $buf .= '  <td><a href="' . $cacheUrl        . '" rel="me">' . 
                              '<img id="' . $cacheSvcName    . '" ' .
                              '    src="' . $cacheSvcFavicon . '" ' . 
                              '    alt="' . $cacheSvcName    . '" /></a></td>';

                //   Column 2: Service Name
                $buf .= '  <td>&nbsp;<a href="' . $cacheUrl     . '" rel="me">' .
                                                  $cacheSvcName . '</a></td>';

                $buf .= '</tr>';
                }
            else
                {
                // Only show the favicon - let them wrap lines
                $buf .= '<a href="' . $cacheUrl        . '" rel="me">' . 
                        '<img id="' . $cacheSvcName    . '" ' .
                        '    src="' . $cacheSvcFavicon . '" ' . 
                        '    alt="' . $cacheSvcName    . '" /></a>&nbsp; &nbsp;';
                }
            }

        // Only use the </table> tag if (show_text_links == TRUE)
        if ($dandyID_options ['show_text_links'] == TRUE)
            {
            $buf .= '</table>';

            // Display the bottom line "Powered by DandyID"
            $buf .= '&nbsp;Powered by <a href="' . DANDYID_URL . '">DandyID</a>';
            }

        // Force a newline after the last line
        $buf .= '<br />';

        fclose ($hCacheFile);
        }

    // $buf will be displayed in the sidebar
    return ($buf);
    }


function dandyIDServices_buildTable ()
    {
    // Build the table and display it
    echo (dandyIDServices_getTable ());
    }


function dandyIDServices_refreshCacheFile ()
    {
    // Instantiate the dandyid class
    $cDandyID = new dandyid ();

    // Load existing options from wp database
    $dandyID_options = get_option (DANDYID_WP_OPTIONS);

    // Set class API fields
    $cDandyID->setAPIFields (DANDYID_API_KEY,
                             DANDYID_API_TOKEN,
                             DANDYID_API_URL);

    // Set class user fields
    $cDandyID->setUserFields ($dandyID_options ['email_address'],
                              $dandyID_options ['email_address'],
                              $dandyID_options ['password']);

    // Sync_user to freshen their data into the API repository.
    $cDandyID->sync_user ();

    // Get the dandy services for the user -- returned as XML
    $return_services_response = $cDandyID->return_services ();

    // Prepare to parse the XML
    $return_services_xml = new SimpleXMLElement ($return_services_response);

    // Create (or overwrite existing) cache file
    if (($hCacheFile = fopen (DANDYID_CACHE_FILE, 'w+')) != FALSE)
        {
        // Parse values -- service by service
        foreach ($return_services_xml->service as $service)
            {
            // Get the dandy service detail for the service
            $service_details_response = $cDandyID->service_details ($service->svcId);

            // Prepare XML return_services_response for parsing
            $service_details_xml = new SimpleXMLElement ($service_details_response);

            // Write each line as CSV - for easy parsing via fgetcsv()
            fprintf ($hCacheFile, "%s, %s, %s\n",
                     $service->url,                               // ex, http://twitter.com/neilsimon
                     $service->svcName,                           // ex. Twitter
                     $service_details_xml->service->svcFavicon);  // ex. http://www.dandyid.org/.../twitter.png
            }

        fclose ($hCacheFile);

        // WP creates a cached-copy of our cache file -- wtf?
        // So... we need to deleting it (upon our refresh), and WP will cache the new file
        if (file_exists (DANDYID_WP_AUTOCOPIED_CACHE_FILE))
            {
            unlink (DANDYID_WP_AUTOCOPIED_CACHE_FILE);
            }
        }
    }


function dandyIDServices_initWidget ()
    {
    // MUST be able to register the widget... else exit
    if (!function_exists ('register_sidebar_widget'))
        {
        return;
        }

    // Declare function -- called from Wordpress -- during page-loads
    function dandyIDServices_widget ($args)
        {
        // Load existing options from wp database
        $dandyID_options = get_option (DANDYID_WP_OPTIONS);

        // Accept parameter array passed-in from Wordpress (e.g. $before_widget, $before_title, etc.)
        // Also, inherits theme CSS styles
        extract ($args);

        // Display sidebar title above the about-to-be-rendered dandy services table
        echo $before_widget . $before_title . $dandyID_options ['sidebarTitle'] . $after_title;

        // Dynamically build the table and display it
        dandyIDServices_buildTable ();
        }

    // Register the widget function to be called from Wordpress on each page-load
    register_sidebar_widget ('DandyID Services', 'dandyIDServices_widget');
    }


function dandyIDServices_updateOptionsPage ()
    {
    // Load existing options from wp database
    $dandyID_options = get_option (DANDYID_WP_OPTIONS);

    // If ALL data fields contain values...
    if (isset ($_POST ['email_address']) &&
        isset ($_POST ['password'])      &&
        isset ($_POST ['user_id'])       &&
        isset ($_POST ['sidebarTitle']))
        {
        //... copy it to the options array
        $dandyID_options ['email_address']   = $_POST ['email_address'];
        $dandyID_options ['password']        = $_POST ['password'];
        $dandyID_options ['user_id']         = $_POST ['user_id'];
        $dandyID_options ['sidebarTitle']    = $_POST ['sidebarTitle'];
        $dandyID_options ['show_text_links'] = $_POST ['show_text_links'] == "TRUE" ? TRUE : FALSE;

        // Store changed options back to wp database
        update_option (DANDYID_WP_OPTIONS, $dandyID_options);

        // Create the initial cache file
        dandyIDServices_refreshCacheFile ();

        // Display update message to user
        echo '<div id="message" class="updated fade"><p>' . "DandyID Service options saved successfully." . '</p></div>';
        }

    // Display the DandyID Service Options form to the user

    echo
     '<div class="wrap">

      <h3>&nbsp; Please enter your DandyID Service Plugin options: &nbsp; (all fields are required)</h3>

      <form action="" method="post">

      <table border="0" cellpadding="10">

      <tr>
      <td>Email:</td>
      <td><input type="text"     name="email_address" value="' . $dandyID_options['email_address'] . '" size="40" /></td>
      <td>The email address you use to logon to DandyID.</td>
      </tr>

      <tr>
      <td>Password:</td>
      <td><input type="password" name="password"      value="' . $dandyID_options['password']      . '" size="40" /></td>
      <td>The password you use to logon to DandyID.</td>
      </tr>

      <tr>
      <td>User ID#:</td>
      <td><input type="text"  name="user_id"       value="' . $dandyID_options['user_id']       . '" size="40" /></td>
      <td>To locate: &nbsp; 1. Logon to <a href="' . DANDYID_URL . '">DandyID</a>. &nbsp; 2. Click on your name. &nbsp; 3. Use the number at the end of the URL in the browser address bar.</td>
      </tr>

      <tr>
      <td>Title:</td>
      <td><input type="text"     name="sidebarTitle"  value="' . $dandyID_options['sidebarTitle']  . '" size="40" /></td>
      <td>The sidebar title to display.</td>
      </tr>

      </table>

      &nbsp; &nbsp;

      <input type="radio" name="show_text_links" value="TRUE"  checked />
      Show Favicons AND Text Links &nbsp; &nbsp; &nbsp;

      <input type="radio" name="show_text_links" value="FALSE" />
      Show Favicons only

      <p>&nbsp;&nbsp;<input type="submit" value="Save" /></p>

      </form>

      </div>';
    }


function dandyIDServices_addOptions ()
    {
    // This is only called once, when the plugin is activated

    // Create the initial array of keys/values
    $dandyID_initial_options = array ('email_address'   => '',
                                      'password'        => '',
                                      'user_id'         => '',
                                      'show_text_links' => '',
                                      'sidebarTitle'    => '');

    // Store the initial array to the wp database
    add_option (DANDYID_WP_OPTIONS, $dandyID_initial_options);
    }


function dandyIDServices_deleteOptions ()
    {
    // This is only called once, when the plugin is deactivated

    // Remove the dandyID_options array from the wp database
    delete_option (DANDYID_WP_OPTIONS);

    // Remove the cache file
    if (file_exists (DANDYID_CACHE_FILE))
        {
        unlink (DANDYID_CACHE_FILE);
        }

    // Remove the WP-cached version of our cache file
    if (file_exists (DANDYID_WP_AUTOCOPIED_CACHE_FILE))
        {
        unlink (DANDYID_WP_AUTOCOPIED_CACHE_FILE);
        }
    }


function dandyIDServices_addSubmenu ()
    {
    // Define the options for the submenu page
    add_submenu_page ('options-general.php',                 // Parent page
                      'DandyID Services page',               // Page title, shown in titlebar
                      'DandyID Services',                    // Menu title
                      10,                                    // Access level all
                      __FILE__,                              // This file displays the options page
                      'dandyIDServices_updateOptionsPage');  // Function that displays the options page
    }


// This callback runs only once, at plugin activation time
register_activation_hook (__FILE__, 'dandyIDServices_addOptions');


// This callback runs only once, at plugin deactivation time
register_deactivation_hook (__FILE__, 'dandyIDServices_deleteOptions');


// This callback runs when adding the widget
add_action ('plugins_loaded', 'dandyIDServices_initWidget');


// This callback runs when adding the submenu
add_action ('admin_menu', 'dandyIDServices_addSubmenu');

?>
