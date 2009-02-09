<?php

/*
Plugin Name: DandyID Services
Plugin URI: http://wordpress.org/extend/plugins/dandyid-services/
Description: Retrieves your <a href="http://dandyid.org">DandyID</a> online identities and displays them as clickable links in your sidebar. After activating this Plugin: (1) Go to Settings -&gt; DandyID Services to configure the required settings, and (2) Go to Design -&gt; Widgets to add the DandyID Services sidebar widget to your sidebar.
Version: 1.2.5
Author: Neil Simon, Sara Czyzewicz, Arron Kallenberg, Dan Perron, Anthony Dimitre
Author URI: http://dandyid.org/
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
define ('DANDYID_URL',                    'http://www.dandyid.org/');
define ('DANDYID_API_KEY',                '17ps6defe5fnem02czzsv95771wu4qe5w5x3');
define ('DANDYID_API_TOKEN',              'hbhvfwjuitwvsvoo5suatq6xgj2cnye6av1p');
define ('DANDYID_SETTINGS_OPTIONS',       'dandyID_settingsOptions');
define ('DANDYID_CACHE_OPTIONS',          'dandyID_cacheOptions');
define ('DANDYID_NEXT_CACHE_TIME_OPTION', 'dandyID_nextCacheTimeOption');
define ('DANDYID_API_URL',                'http://www.dandyid.org/api/');
define ('DANDYID_CACHE_TIME_STRING',      'Y-m-d-H-i');
define ('DANDYID_CACHE_REFRESH_INTERVAL', '+2 hours');


// Sidebar content to show
define ('DANDYID_SHOW_FAVICONS_AND_TEXTLINKS', 0);
define ('DANDYID_SHOW_FAVICONS',               1);
define ('DANDYID_SHOW_TEXTLINKS',              2);


// Global data -- used exclusively by dandyIDServices_xml*(), and dandyIDServices_refreshCache().
// These globals are required, due to the way xml_*() callbacks are implemented by PHP.
$gInsideSERVICE = FALSE;
$gTag           = '';
$gUrl           = '';
$gSvcName       = '';
$gSvcFavicon    = '';
$gIndex         = 0;
$gCacheOptions  = array (array ('url' => '', 'svcName' => '', 'svcFavicon' => ''));
$gDandyIDClass  = 0;


function dandyIDServices_getTable ()
    {
    // If the current time exceeds the stored refresh-interval time...
    if (date (DANDYID_CACHE_TIME_STRING) > get_option (DANDYID_NEXT_CACHE_TIME_OPTION))
        {
        // Overwrite the existing cache, reset the cache time to current time + defined interval
        dandyIDServices_refreshCache ();
        }

    // Load existing settings options from wp database
    $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

    // Initialize output buffer (returned by this function)
    $buf = '';

    // Begin div tag: "dandyIDSidebarIdentities" -- to enable css stying
    $buf .= '<div id="dandyIDSidebarIdentities">';

    // Get the cache from the wp database
    $cacheOptions = get_option (DANDYID_CACHE_OPTIONS);

    // Add services
    for ($i = 0; $cacheOptions [$i] ['url'] != ''; $i++)
        {
        // Get next cache row
        $cacheUrl        = $cacheOptions [$i] ['url'];
        $cacheSvcName    = $cacheOptions [$i] ['svcName'];
        $cacheSvcFavicon = $cacheOptions [$i] ['svcFavicon'];

        // Either show favicon AND text link...
        if ($dandyID_settingsOptions ['show_style'] == DANDYID_SHOW_FAVICONS_AND_TEXTLINKS)
            {
            // Column 1: Favicon (force 2 trailing spaces)
            $buf .= '&nbsp;<a href="' . $cacheUrl        . '" rel="me">' . 
                          '<img id="' . $cacheSvcName    . '" ' .
                          '    src="' . $cacheSvcFavicon . '" ' . 
                          '    width="16"  '             . 
                          '    height="16" '             . 
                          '    alt="' . $cacheSvcName    . '" /></a> &nbsp;';

            // Column 2: Text-link (position text to top, to align better with the favicon)
            $buf .= '<span style="vertical-align: top;">';
            $buf .= '<a href="' . $cacheUrl     . '" rel="me">' .
                                  $cacheSvcName . '</a>';
            $buf .= '</span><br />';
            }

        // ... or only show the favicon
        else if ($dandyID_settingsOptions ['show_style'] == DANDYID_SHOW_FAVICONS)
            {
            // let them wrap lines (force 1 trailing space after each favicon)
            $buf .= '<a href="' . $cacheUrl        . '" rel="me">' . 
                    '<img id="' . $cacheSvcName    . '" ' .
                    '    src="' . $cacheSvcFavicon . '" ' . 
                    '    width="16"  '                    . 
                    '    height="16" '                    . 
                    '    alt="' . $cacheSvcName    . '" /></a>&nbsp;';
            }

        // ... or only show the text links
        else   // must be DANDYID_SHOW_TEXTLINKS
            {
            // each on a separate line
            $buf .= '<a href="' . $cacheUrl     . '" rel="me">' .
                                  $cacheSvcName . '</a><br />';
            }
        }

    // If (show_powered_by == TRUE), display the "Powered by DandyID" line
    if ($dandyID_settingsOptions ['show_powered_by'] == TRUE)
        {
        // Begin div tag: "dandyIDSidebarPoweredBy" -- to enable css stying
        $buf .= '<div id="dandyIDSidebarPoweredBy" style="font-size:.75em">';

        // Display the bottom line "Powered by DandyID"
        $buf .= 'Powered by <a href="' . DANDYID_URL . '">DandyID</a>';

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
    // Load existing settings options from wp database
    $dandyID_settingsOptions = get_option (DANDYID_SETTINGS_OPTIONS);

    if ($dandyID_settingsOptions ['email_address'] == '')
        {
        // Email has not been entered.
        // Do not attempt to retrieve DandyID online identities.

        // Reset cache settings to null
        $nullCacheOptions = array (array ('url' => '', 'svcName' => '', 'svcFavicon' => ''));

        // Store the cache options array to wp database
        update_option (DANDYID_CACHE_OPTIONS, $nullCacheOptions);

        // Reset the next cache time to the (current time + defined interval), store to wp database
        update_option (DANDYID_NEXT_CACHE_TIME_OPTION,
                       date (DANDYID_CACHE_TIME_STRING, strtotime (DANDYID_CACHE_REFRESH_INTERVAL)));
        }

    else
        {
        global $gDandyIDClass;

        // Instantiate the dandyid class
        $gDandyIDClass = new dandyid ();

        // Set class API fields
        $gDandyIDClass->setAPIFields (DANDYID_API_KEY,
                                      DANDYID_API_TOKEN,
                                      DANDYID_API_URL);

        // Set class user fields
        $gDandyIDClass->setUserFields ($dandyID_settingsOptions ['email_address'],
                                       $dandyID_settingsOptions ['email_address']);

        // Get the dandy services for the user -- returned as XML
        if (($return_services_response = $gDandyIDClass->return_services ()) == FALSE)
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
            global $gIndex, $gInsideSERVICE, $gCacheOptions;

            // Initialize XML-related globals
            $gIndex         = 0;
            $gInsideSERVICE = FALSE;

            // Initialize cache settings to null -- the XML parsing will load this up
            $gCacheOptions = array (array ('url' => '', 'svcName' => '', 'svcFavicon' => ''));

            // Example xml returned by return_services():
            //
            // <xml version="1.0" encoding="iso-8859-1">
            // <services>
            //   <service>
            //     <svcId>delicious</svcId>
            //     <svcName>Delicious</svcName>
            //     <usrSvcId>nsimon</usrSvcId>
            //     <url>http://delicious.com/nsimon</url>
            //     <svcFavicon>http://www.dandyid.org/code/images/miscellaneous/favicons/delicious.png</svcFavicon>
            //   </service>
            //   <service>
            //     <svcId>twitter</svcId>
            //     <svcName>Twitter</svcName>
            //     <usrSvcId>neilsimon</usrSvcId>
            //     <url>http://twitter.com/neilsimon</url>
            //     <svcFavicon>http://www.dandyid.org/code/images/miscellaneous/favicons/twitter.png</svcFavicon>
            //   </service>
            // </services>

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

            // Reset the next cache time to the (current time + defined interval), store to wp database
            update_option (DANDYID_NEXT_CACHE_TIME_OPTION,
                           date (DANDYID_CACHE_TIME_STRING, strtotime (DANDYID_CACHE_REFRESH_INTERVAL)));
            }
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
        isset ($_POST ['sidebarTitle']))
        {
        //... copy the fields to the persistent wp options array
        $dandyID_settingsOptions ['email_address']   = $_POST ['email_address'];
        $dandyID_settingsOptions ['sidebarTitle']    = $_POST ['sidebarTitle'];
        $dandyID_settingsOptions ['show_powered_by'] = $_POST ['show_powered_by'] == "TRUE" ? TRUE : FALSE;

        if ($_POST ['show_style'] == "BOTH")
            {
            $dandyID_settingsOptions ['show_style'] = DANDYID_SHOW_FAVICONS_AND_TEXTLINKS;
            }

        else if ($_POST ['show_style'] == "FAVICONS")
            {
            $dandyID_settingsOptions ['show_style'] = DANDYID_SHOW_FAVICONS;
            }

        else   // must be TEXTLINKS
            {
            $dandyID_settingsOptions ['show_style'] = DANDYID_SHOW_TEXTLINKS;
            }

        // Store changed options back to wp database
        update_option (DANDYID_SETTINGS_OPTIONS, $dandyID_settingsOptions);

        // Force the cache to be created (or overwritten)
        dandyIDServices_refreshCache ();

        // Display update message to user
        echo '<div id="message" class="updated fade"><p>' . "DandyID Service options saved successfully." . '</p></div>';
        }

    // Initialize data fields for "show_style" radio button
    $showFaviconsAndTextlinks = "";
    $showFavicons             = "";
    $showTextlinks            = "";

    // Set variable for form to use for "show_style" to show sticky-value for radio button
    if ($dandyID_settingsOptions ['show_style'] == DANDYID_SHOW_FAVICONS_AND_TEXTLINKS)
        {
        $showFaviconsAndTextlinks = "checked";
        }

    else if ($dandyID_settingsOptions ['show_style'] == DANDYID_SHOW_FAVICONS)
        {
        $showFavicons = "checked";
        }

    else // must be DANDYID_SHOW_TEXTLINKS
        {
        $showTextlinks = "checked";
        }

    // Initialize data fields for "show_powered_by" radio button
    $showPoweredBy = "";
    $hidePoweredBy = "";

    // Set variable for form to use for "show_powered_by" to show sticky-value for radio button
    if ($dandyID_settingsOptions ['show_powered_by'] == TRUE)
        {
        $showPoweredBy = "checked";
        }
    else
        {
        $hidePoweredBy = "checked";
        }

    // Display the DandyID Service Options form to the user

    echo
     '<div class="wrap">

      <h3>&nbsp; Please enter your DandyID Service Plugin options:</h3>

      <form action="" method="post">

      <table border="0" cellpadding="10">

      <tr>
      <td>Email:</td>
      <td><input type="text" name="email_address" value="' . $dandyID_settingsOptions ['email_address'] . '" size="40" /></td>
      <td>The email address you use to logon to DandyID.</td>
      </tr>

      <tr>
      <td>Title:</td>
      <td><input type="text" name="sidebarTitle"  value="' . $dandyID_settingsOptions ['sidebarTitle']  . '" size="40" /></td>
      <td>The sidebar title to display.</td>
      </tr>

      </table>

      <table border="0" cellpadding="10">

      <tr>
      <td width="300"><input type="radio" name="show_style" value="BOTH"      ' . $showFaviconsAndTextlinks . ' />
      Show Favicons and Text-links<br />
                      <input type="radio" name="show_style" value="FAVICONS"  ' . $showFavicons             . ' />
      Show Favicons only<br />
                      <input type="radio" name="show_style" value="TEXTLINKS" ' . $showTextlinks            . ' />
      Show Text-links only</td>

      <td width="300" valign="top"><input type="radio" name="show_powered_by" value="TRUE"  ' . $showPoweredBy   . ' />
      Show "Powered by DandyID"<br />
                      <input type="radio" name="show_powered_by" value="FALSE" ' . $hidePoweredBy   . ' />
      Hide "Powered by DandyID"</td>
      </tr>

      </table>

      <p>&nbsp;&nbsp;<input type="submit" value="Save" /></p>

      </form>

      <h5>&nbsp; &nbsp; ( <a href="http://wordpress.org/extend/plugins/dandyid-services/other_notes/">View Change Log</a> of past updates to the DandyID Services Plugin. )</h5>

      </div>';
    }


function dandyIDServices_createOptions ()
    {
    // This is only called once, when the plugin is activated

    // Get the wp logged-in user's email address to use as default in options page
    global $current_user;
    get_currentuserinfo ();

    // Create the initialSettingsOptions array of keys/values
    // First time user sees form, default to "Show Favicons and Text Links"
    // Set 'show_style' default to DANDYID_SHOW_FAVICONS_AND_TEXTLINKS (i.e. 0)
    $dandyID_initialSettingsOptions = array ('email_address'   => $current_user->user_email,
                                             'show_style'      => 0,
                                             'show_powered_by' => 'TRUE',
                                             'sidebarTitle'    => '');

    // Create the initialCacheOptions 2-dimensional array of keys/values
    $dandyID_initialCacheOptions = array (array ('url'        => '',
                                                 'svcName'    => '',
                                                 'svcFavicon' => ''));

    // Set the initial cache time to null
    $dandyID_initialCacheDateOption = '';

    // Store the initial options to the wp database
    add_option (DANDYID_SETTINGS_OPTIONS,       $dandyID_initialSettingsOptions);
    add_option (DANDYID_CACHE_OPTIONS,          $dandyID_initialCacheOptions);
    add_option (DANDYID_NEXT_CACHE_TIME_OPTION, $dandyID_initialCacheDateOption);
    }


function dandyIDServices_deleteOptions ()
    {
    // This is only called once, when the plugin is deactivated

    // Remove the dandyID_settingsOptions array from the wp database
    delete_option (DANDYID_SETTINGS_OPTIONS);

    // Remove from the wp database
    delete_option (DANDYID_CACHE_OPTIONS);

    // Remove from the wp database
    delete_option (DANDYID_NEXT_CACHE_TIME_OPTION);
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


// dandyIDServices_createOptions() ... runs only once, at activation time
register_activation_hook (__FILE__, 'dandyIDServices_createOptions');


// dandyIDServices_deleteOptions() ... runs only once, at deactivation time
register_deactivation_hook (__FILE__, 'dandyIDServices_deleteOptions');


// dandyIDServices_initWidget() ...... load the widget, show it in the widget control in the admin section
add_action ('plugins_loaded', 'dandyIDServices_initWidget');


// dandyIDServices_addSubmenu() ...... add the "DandyID Services" submenu to the "Setting" admin page
add_action ('admin_menu', 'dandyIDServices_addSubmenu');

?>
