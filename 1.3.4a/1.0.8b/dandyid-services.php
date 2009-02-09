<?php

/*
Plugin Name: DandyID Services
Plugin URI: http://solidcode.com/
Description: Retrieves your <a href="http://dandyid.org">DandyID</a> online identities and displays them as clickable links in your sidebar. After activating this Plugin: (1) Go to <a href="options-general.php?page=dandyid-services/dandyid-services.php">Settings -&gt; DandyID Services</a> to configure the required settings, and (2) Go to <a href="widgets.php">Design -&gt; Widgets</a> to add the DandyID Services sidebar widget to your sidebar.
Version: 1.0.8
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
define ('DANDYID_URL',               'http://www.dandyid.org/');
define ('DANDYID_PROFILE_URL',       'http://www.dandyid.org/beta/users/user_id/');
define ('DANDYID_MINI',              'http://www.dandyid.org/code/images/miscellaneous/favicons/dandyidmini.png');
define ('DANDYID_API_KEY',           '17ps6defe5fnem02czzsv95771wu4qe5w5x3');
define ('DANDYID_API_TOKEN',         'hbhvfwjuitwvsvoo5suatq6xgj2cnye6av1p');
define ('DANDYID_SETTINGS_OPTIONS',  'dandyID_settingsOptions');
define ('DANDYID_CACHE_OPTIONS',     'dandyID_cacheOptions');
define ('DANDYID_CACHE_DATE_OPTION', 'dandyID_cacheDateOption');
define ('DANDYID_API_URL',           'http://www.dandyid.org/api/');


// Global data -- used exclusively by dandyIDServices_xml*(), and dandyIDServices_refreshCache().
// These globals are required, due to the way xml_*() callbacks are implemented by PHP.
$gInsideSERVICE = FALSE;
$gTag           = '';
$gUrl           = '';
$gSvcName       = '';
$gSvcFavicon    = '';
$gIndex         = 0;
$gCacheOptions  = array (array ('url' => '', 'svcName' => '', 'svcFavicon' => ''));
$gDandyID       = 0;


function dandyIDServices_getTable ()
    {
    // If the cacheDate doesn't match the current date...
    if (get_option (DANDYID_CACHE_DATE_OPTION) != date ('Y-m-d'))
        {
        // Overwrite the existing cache, reset the cacheDate to current date)
        dandyIDServices_refreshCache ();
        }

    // Load existing settings options from wp database
    $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

    // Initialize output buffer (returned by this function)
    $buf = '';

    // Begin div tag: "dandyIDSidebarIdentities" -- to enable css stying
    $buf .= '<div class="dandyIDSidebarIdentities">';

    // Display the DandyID-Mini chicklet, with link to the users profile
    $buf .= '<a href="' . DANDYID_PROFILE_URL . $dandyID_settingsOptions ['user_id'] .
            '"><img src="' . DANDYID_MINI . '" /></a>&nbsp;';

    // Get the cache from the wp database
    $cacheOptions = get_option (DANDYID_CACHE_OPTIONS);

    // Only use the <table> tag if (show_text_links == TRUE)
    if ($dandyID_settingsOptions ['show_text_links'] == TRUE)
        {
        // BEGIN table: one line for EACH service, containing chicklet and text link to service
        $buf .= '<table border="0" cellspacing="4">';
        }

    // Add services
    for ($i = 0; $cacheOptions [$i] ['url'] != ''; $i++)
        {
        // Get next cache row
        $cacheUrl        = $cacheOptions [$i] ['url'];
        $cacheSvcName    = $cacheOptions [$i] ['svcName'];
        $cacheSvcFavicon = $cacheOptions [$i] ['svcFavicon'];

        // Either show favicon AND text link...
        if ($dandyID_settingsOptions ['show_text_links'] == TRUE)
            {
            // Each table row will have 2 columns: svcFavicon, svcName (each links to user service url)

            // Column 1: Service Favicon
            $buf .= '<tr>';
            $buf .= '  <td><a href="' . $cacheUrl        . '" rel="me">' . 
                          '<img id="' . $cacheSvcName    . '" ' .
                          '    src="' . $cacheSvcFavicon . '" ' . 
                          '    alt="' . $cacheSvcName    . '" /></a></td>';

            // Column 2: Service Name
            $buf .= '  <td>&nbsp;<a href="' . $cacheUrl     . '" rel="me">' .
                                              $cacheSvcName . '</a></td>';

            $buf .= '</tr>';
            }

        // ... or only show the favicon
        else
            {
            // let them wrap lines
            $buf .= '<a href="' . $cacheUrl        . '" rel="me">' . 
                    '<img id="' . $cacheSvcName    . '" ' .
                    '    src="' . $cacheSvcFavicon . '" ' . 
                    '    alt="' . $cacheSvcName    . '" /></a>&nbsp;';
            }
        }

    // Only use the </table> tag if (show_text_links == TRUE)
    if ($dandyID_settingsOptions ['show_text_links'] == TRUE)
        {
        $buf .= '</table>';

        // Begin div tag: "dandyIDSidebarPoweredBy" -- to enable css stying
        $buf .= '<div class="dandyIDSidebarPoweredBy">';

        // Display the bottom line "Powered by DandyID"
        $buf .= '&nbsp;Powered by <a href="' . DANDYID_URL . '">DandyID</a>';

        // End div tag: "dandyIDSidebarPoweredBy"
        $buf .= '</div>';
        }

    // Force a newline after the last line
    $buf .= '<br />';

    // Close div tag: "dandyIDSidebarIdentities"
    $buf .= '</div>';

    // $buf will be displayed in the sidebar
    return ($buf);
    }


function dandyIDServices_buildTable ()
    {
    // Build the table and display it
    echo (dandyIDServices_getTable ());
    }


function dandyIDServices_refreshCache ()
    {
    global $gInsideSERVICE, $gIndex, $gDandyID;

    // Initialize XML-handler globals
    $gIndex         = 0;
    $gInsideSERVICE = FALSE;

    // Instantiate the dandyid class
    $gDandyID = new dandyid ();

    // Load existing options from wp database
    $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

    // Set class API fields
    $gDandyID->setAPIFields (DANDYID_API_KEY,
                             DANDYID_API_TOKEN,
                             DANDYID_API_URL);

    // Set class user fields
    $gDandyID->setUserFields ($dandyID_settingsOptions ['email_address'],
                              $dandyID_settingsOptions ['email_address'],
                              $dandyID_settingsOptions ['password']);

    // Sync_user to freshen their data into the API repository.
    $gDandyID->sync_user ();

    // Get the dandy services for the user -- returned as XML
    if (($return_services_response = $gDandyID->return_services ()) == FALSE)
        {
        // This can happen when the site is found, but the API is down
        // Leave the existing cache intact -- just exit
        }

    elseif (strpos ($return_services_response, "Not Found"))
        {
        // This can happen when the site is NOT found
        // Leave the existing cache intact -- just exit
        }

    else
        {
        global $gCacheOptions;

        // Initialize global cache options array to null
        $gCacheOptions  = array (array ('url' => '', 'svcName' => '', 'svcFavicon' => ''));

        // Prepare to parse
        $xmlParser = xml_parser_create ();

        // Define XML callback -- called via xml_parse()
        xml_set_element_handler ($xmlParser,
                                 'dandyIDServices_xmlStartElement',
                                 'dandyIDServices_xmlEndElement');


        // Define XML callback -- called via xml_parse()
        xml_set_character_data_handler ($xmlParser, 'dandyIDServices_xmlData');

        // Parse the XML -- all data will be stored into gCacheOptions
        xml_parse ($xmlParser, $return_services_response, TRUE);

        // Set last item url in gCacheOptions to null
        $gCacheOptions [$gIndex] ['url'] = '';

        // Release parser resources
        xml_parser_free ($xmlParser);

        // Store the cache options array to wp database
        update_option (DANDYID_CACHE_OPTIONS, $gCacheOptions);

        // Reset the cache date to the current date, store to wp database
        update_option (DANDYID_CACHE_DATE_OPTION, date ('Y-m-d'));
        }
    }


function dandyIDServices_initWidget ()
    {
    // MUST be able to register the widget... else exit
    if (function_exists ('register_sidebar_widget'))
        {
        // Declare function -- called from Wordpress -- during page-loads
        function dandyIDServices_widget ($args)
            {
            // Load existing options from wp database
            $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

            // Accept parameter array passed-in from Wordpress (e.g. $before_widget, $before_title, etc.)
            // Also, inherits theme CSS styles
            extract ($args);

            // Display sidebar title above the about-to-be-rendered dandy services table
            echo $before_widget . $before_title . $dandyID_settingsOptions ['sidebarTitle'] . $after_title;

            // Dynamically build the table and display it
            dandyIDServices_buildTable ();
            }

        // Register the widget function to be called from Wordpress on each page-load
        register_sidebar_widget ('DandyID Services', 'dandyIDServices_widget');
        }
    }


function dandyIDServices_updateSettingsOptionsPage ()
    {
    // Load existing options from wp database
    $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

    // If ALL data fields contain values...
    if (isset ($_POST ['email_address']) &&
        isset ($_POST ['password'])      &&
        isset ($_POST ['user_id'])       &&
        isset ($_POST ['sidebarTitle']))
        {
        //... copy the fields to the persistent wp options array
        $dandyID_settingsOptions ['email_address']   = $_POST ['email_address'];
        $dandyID_settingsOptions ['password']        = $_POST ['password'];
        $dandyID_settingsOptions ['user_id']         = $_POST ['user_id'];
        $dandyID_settingsOptions ['sidebarTitle']    = $_POST ['sidebarTitle'];
        $dandyID_settingsOptions ['show_text_links'] = $_POST ['show_text_links'] == "TRUE" ? TRUE : FALSE;

        // Store changed options back to wp database
        update_option (DANDYID_SETTINGS_OPTIONS, $dandyID_settingsOptions);

        // Force the cache to be created (or overwritten)
        dandyIDServices_refreshCache ();

        // Display update message to user
        echo '<div id="message" class="updated fade"><p>' . "DandyID Service options saved successfully." . '</p></div>';
        }

    // Set variable for form to use to show sticky-value for radio button
    if ($dandyID_settingsOptions ['show_text_links'] == TRUE)
        {
        $showFavsAndText = "checked";
        $showFavsOnly    = "";
        }
    else
        {
        $showFavsAndText = "";
        $showFavsOnly    = "checked";
        }

    // Display the DandyID Service Options form to the user

    echo
     '<div class="wrap">

      <h3>&nbsp; Please enter your DandyID Service Plugin options: &nbsp; (all fields are required)</h3>

      <form action="" method="post">

      <table border="0" cellpadding="10">

      <tr>
      <td>Email:</td>
      <td><input type="text"     name="email_address" value="' . $dandyID_settingsOptions ['email_address'] . '" size="40" /></td>
      <td>The email address you use to logon to DandyID.</td>
      </tr>

      <tr>
      <td>Password:</td>
      <td><input type="password" name="password"      value="' . $dandyID_settingsOptions ['password']      . '" size="40" /></td>
      <td>The password you use to logon to DandyID.</td>
      </tr>

      <tr>
      <td>User ID#:</td>
      <td><input type="text"  name="user_id"       value="' . $dandyID_settingsOptions ['user_id']       . '" size="40" /></td>
      <td>To locate: &nbsp; 1. Logon to <a href="' . DANDYID_URL . '">DandyID</a>. &nbsp; 2. Click on your name. &nbsp; 3. Use the number at the end of the URL in the browser address bar.</td>
      </tr>

      <tr>
      <td>Title:</td>
      <td><input type="text"     name="sidebarTitle"  value="' . $dandyID_settingsOptions ['sidebarTitle']  . '" size="40" /></td>
      <td>The sidebar title to display.</td>
      </tr>

      </table>

      &nbsp; &nbsp;

      <input type="radio" name="show_text_links" value="TRUE"  ' . $showFavsAndText . ' />
      Show Favicons and Text Links &nbsp; &nbsp; &nbsp;

      <input type="radio" name="show_text_links" value="FALSE" ' . $showFavsOnly    . ' />
      Show Favicons only

      <p>&nbsp;&nbsp;<input type="submit" value="Save" /></p>

      </form>

      </div>';
    }


function dandyIDServices_createOptions ()
    {
    // This is only called once, when the plugin is activated

    // Create the initialSettingsOptions array of keys/values
    // First time user sees form, default to "Show Favicons and Text Links"
    $dandyID_initialSettingsOptions = array ('email_address'   => '',
                                             'password'        => '',
                                             'user_id'         => '',
                                             'show_text_links' => 'TRUE',
                                             'sidebarTitle'    => '');

    // Create the initialCacheOptions 2-dimensional array of keys/values
    $dandyID_initialCacheOptions = array (array ('url'        => '',
                                                 'svcName'    => '',
                                                 'svcFavicon' => ''));

    // Set the initial cache date to null
    $dandyID_initialCacheDateOption = '';

    // Store the initial options to the wp database
    add_option (DANDYID_SETTINGS_OPTIONS,  $dandyID_initialSettingsOptions);
    add_option (DANDYID_CACHE_OPTIONS,     $dandyID_initialCacheOptions);
    add_option (DANDYID_CACHE_DATE_OPTION, $dandyID_initialCacheDateOption);
    }


function dandyIDServices_deleteOptions ()
    {
    // This is only called once, when the plugin is deactivated

    // Remove the dandyID_settingsOptions array from the wp database
    delete_option (DANDYID_SETTINGS_OPTIONS);

    // Remove the dandyID_cacheOptions array from the wp database
    delete_option (DANDYID_CACHE_OPTIONS);

    // Remove the dandyID_cacheDateOption from the wp database
    delete_option (DANDYID_CACHE_DATE_OPTION);
    }


function dandyIDServices_addSubmenu ()
    {
    // Define the options for the submenu page
    add_submenu_page ('options-general.php',                         // Parent page
                      'DandyID Services page',                       // Page title, shown in titlebar
                      'DandyID Services',                            // Menu title
                      10,                                            // Access level all
                      __FILE__,                                      // This file displays the options page
                      'dandyIDServices_updateSettingsOptionsPage');  // Function that displays options page
    }


function dandyIDServices_xmlStartElement ($parser, $name, $attrs)
    {
    global $gInsideSERVICE, $gTag;

    // This function gets called each time <xxxx> (any start element tag) encountered

    // If inside a subtag of <service>...
    if ($gInsideSERVICE)
        {
        // Possible values for gTag can be: SVCID, SVCNAME, USERSVCID, or SVCFAVICON
        // NOTE: PHP converts the tags to uppercase
        $gTag = $name;
        }

    // If not already within the <service> tag, check to see if this is a <service> tag
    elseif ($name == 'SERVICE')
        {
        // Let dandyIDServices_xmlData() know that we're inside the SERVICE tag
        $gInsideSERVICE = TRUE;
        }
    }


function dandyIDServices_xmlEndElement ($parser, $name)
    {
    global $gInsideSERVICE, $gUrl, $gSvcName, $gSvcFavicon, $gCacheOptions, $gIndex;

    // This function gets called each time ANY </xxxx> is encountered

    // When xml_parser() detects </service>...
    if ($name == 'SERVICE')
        {
        // Update global cache vars from global data fields
        $gCacheOptions [$gIndex] ['url']        = $gUrl;
        $gCacheOptions [$gIndex] ['svcName']    = $gSvcName;
        $gCacheOptions [$gIndex] ['svcFavicon'] = $gSvcFavicon;

        // Clear global data fields
        $gUrl           = '';
        $gSvcName       = '';
        $gSvcFavicon    = '';
        $gInsideSERVICE = FALSE;
        $gIndex++;
        }
    }


function dandyIDServices_xmlData ($parser, $data)
    {
    global $gInsideSERVICE, $gTag, $gUrl, $gSvcName, $gSvcFavicon;

    // This function gets called iteratively, one time per each subtag with <service>

    // Process the subtag -- save the set of values to globals
    if ($gInsideSERVICE)
        {
        switch ($gTag)
            {
            case 'URL':
                $gUrl .= $data;
                break;

            case 'SVCNAME':
                $gSvcName .= $data;
                break;

            case 'SVCFAVICON':
                $gSvcFavicon .= $data;
                break;
            }
        }
    }


// This callback runs only once, at plugin activation time
register_activation_hook (__FILE__, 'dandyIDServices_createOptions');


// This callback runs only once, at plugin deactivation time
register_deactivation_hook (__FILE__, 'dandyIDServices_deleteOptions');


// This callback runs when adding the widget
add_action ('plugins_loaded', 'dandyIDServices_initWidget');


// This callback runs when adding the submenu
add_action ('admin_menu', 'dandyIDServices_addSubmenu');

?>
