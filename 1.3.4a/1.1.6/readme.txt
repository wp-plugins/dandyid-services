=== DandyID Services ===
Contributors: Neil Simon, Sara Czyzewicz, Arron Kallenberg, Dan Perron, Anthony Dimitre
Tags: admin, bookmark, bookmarking, bookmarks, community, Dandy, DandyID, Delicious, email, e-mail, favicon, favicons, Facebook, Flickr, identity, Google, LinkedIn, manage, myspace, OAuth, OpenID, profile, services, sidebar, social, social bookmarking, social bookmarks, Twitter, widget, Yahoo, YouTube
Requires at least: 2.0
Tested up to: 2.7
Stable tag: trunk

Displays all of your online identities (Twitter, Facebook, Flickr, LinkedIn, YouTube, more) as clickable links in your sidebar.

== Description ==

**[DandyID](http://dandyid.org/) is a free service that enables you to easily connect all of your online identities.**

**The DandyID Services Plugin:**

* Retrieves all of your online identities from DandyID.
* Displays them in your sidebar as convenient **clickable links**.
* Please see the [Screenshots](http://wordpress.org/extend/Plugins/dandyid-services/screenshots/) for examples.

When you update your online identities on DandyID, they are automatically updated in your sidebar.

**DandyID currently supports over 180 services including:**

* 30 Boxes
* 43 People
* 43 Places
* 43 Things
* ActiveRain
* Adocu
* AIM
* All Consuming
* Amazon
* Amie Street
* Ask500People
* Badoo
* Bebo
* Behance Network
* Big Contact
* Blinklist
* Blip.fm
* Blip.tv
* Blippr
* Blogger
* Bloglines
* Blue Dot
* Box.net
* Break
* Brightkite
* Bring Light
* Bungie.net
* Buzznet
* Change.org
* Chickipedia
* claimID
* Clipmarks
* Cluztr
* coComment
* Corkd
* Crackle
* Crusher
* Curse
* Dailymotion
* Delicious
* deviantART
* digFoot
* Digg
* Diigo
* Disqus
* Dodgeball
* Dopplr
* Dotspotter
* Ebay
* esnips
* Facebook
* Finetune
* Flickr
* Flixster
* Fotolog
* FriendFeed
* Friendster
* Funny or Die
* Furl
* Fuzz
* GameSpot
* Get Satisfaction
* Glogster
* Goodreads
* Google Reader
* Groovr
* Guitar Hero
* Gyminee
* hi5
* HelloTxt
* Howcast
* Hulu
* Hype Machine
* identi.ca
* identoo
* iJigg
* iminta
* Instructables
* Intense Debate
* iReport
* Jaiku
* JamBase
* Jumpcut
* Kirtsy
* Kiva
* Lala
* last.fm
* lifestream.fm
* Lijit
* LinkedIn
* LiveJournal
* Ma.gnolia
* Multiply
* MyBlogLog
* My Mashable
* MySpace
* Naymz
* Netflix
* Newsvine
* Ning
* Odeo
* Orkut
* Pandora
* Photobucket
* Picasa
* Plaxo
* Plurk
* Propeller
* QIK
* Reddit
* RedWire
* RI Nexus
* RockBand
* Ryze
* Shelfari
* Seesmic
* Skitch
* SlideShare
* Socialthing
* soup.io
* Squidoo
* Streem
* Stumbleupon
* Swurl
* Technorati
* The DJ List
* Thesixtyone
* ThisNext
* Threadless
* TripAdvisor
* Tripit
* Trulia
* Tumblr
* Twello
* Twine
* Twitpic
* Twitter
* Twitxr
* TypeKey
* Upcoming
* Veoh
* Viddler
* Vimeo
* Virb
* Vi.sualize.us
* Vox
* Wakoopa
* We Heart It
* Webshots
* Wink
* Xbox LIVE
* Xing
* Yahoo! 360
* Yahoo! Answers
* Yahoo! Live
* Yelp
* YouTube
* YowTrip
* Zillow
* ZoomInfo
* Zorpia
* more being added

To learn more, please visit [DandyID](http://dandyid.org/).

For the latest news, please follow [@DandyID](http://twitter.com/dandyid) on Twitter. 

== Installation ==

**Upgrading?**

* Please **Deactivate** the previous DandyID Services Plugin first.

**Experiencing errors?**

* Please visit our [Customer Service and Support Page](http://getsatisfaction.com/dandyid/products/dandyid_wordpress_Plugin).
* We welcome your questions, comments and suggestions.

**Pre-Installation: Setup Your Free Account on DandyID**

1. Signup at [DandyID](http://dandyid.org/).

2. Setup all of your online identities: Twitter, Facebook, Flickr, ...

**Installation**

1. Upload the DandyID Services Plugin folder to **/wp-content/Plugins/**

2. Login to your WP admin web page.

3. Activate the Plugin:
   - Click on the **Plugins** tab.
   - Find DandyID Services in the list of Inactive Plugins (or Recently Active Plugins).
   - Click on **Activate** to activate the DandyID Services Plugin.

4. Configure the Plugin:
   - Click on the **Settings** tab.
   - Click on the **DandyID Services** subtab.
   - Enter your DandyID Service Options -- all fields are required.
   - Press the **Save** button to save your DandyID Service Options.

5. Setup as a sidebar widget:
   - Click on the **Design** tab.
   - Click on the **Widgets** subtab.
   - On the left side, next to DandyID Services, click on **Add** to make it appear in the list of **Current Widgets**.
   - Click on **Save Changes**.

6. If your theme DOES NOT support widgets, place this line of code in your sidebar code (e.g. sidebar.php):
   - &lt;?php dandyIDServices_buildTable (); ?&gt;

7. Your DandyID online identities will appear as clickable links in your sidebar.

== Frequently Asked Questions ==

**How often does the Plugin retrieve my list of DandyID services ?**

Your DandyID services are retrieved once every 2 hours. This aids performance to help blog pages load faster.

**If I update my services on DandyID, does the Plugin wait until the next 2-hour interval to retrieve them ?**

Yes. But you can initiate a refresh at any time by going to **Settings->DandyID Services**, and pressing the **Save** button.

**Known Issues and Workarounds**

1. Fatal error: Call to undefined function: curl_init()
   - The CURL PHP library needs to be installed.
   - [Please see these setup instructions](http://php.net/manual/en/curl.setup.php) for more info.

== Screenshots ==

1. Favicons with text links shown in the sidebar.

2. Favicons-only shown in the sidebar.

3. The DandyID Services settings page.

== Change History ==

**Rev 1.1.6**  2009-Jan-16

* Suppress activation warning on curl_setopt() CURLOPT_FOLLOWLOCATION.

**Rev 1.1.5**  2009-Jan-16

* Group options on settings page for improved usability.

**Rev 1.1.4**  2009-Jan-15

* Make the "Powered by DandyID" line a configurable option.
* Written and tested [@TechStars](http://twitter.com/techstars) [#hackspace](http://search.twitter.com/search?q=%23hackspace), Boulder, Colorado.

**Rev 1.1.3**  2009-Jan-12

* Change the cache refresh interval from every 24 hours to every 2 hours.
* During a refresh, the plugin retrieves the list of DandyID services, and stores them in the WordPress database.

**Rev 1.1.2**  2009-Jan-10

* Reduced the font size of "Powered by DandyID" line.

**Rev 1.1.1**  2009-Jan-09

* Options page: removed password requirement to simplify setup.
* Options page: removed user_id requirement to simplify setup.
* User's public identities profile is now retrieved from DandyID.
* User's DandyID Service now returned as one of the services.
* Larger DandyID-mini icon replaced with 15x15 favicon -- for continuity with other service icons.

**Rev 1.1.0**  2009-Jan-06

* Preserve local online-identities cache if DandyID API is down, or site is not available.

**Rev 1.0.9**  2009-Jan-06

* If no setup credentials are configured, do not attempt to retrieve DandyID online identities.
* If no user_id is configured, link the mini-chicklet to the DandyID home page.

**Rev 1.0.8**  2009-Jan-04

* Converted SimpleXMLElement() calls to xml_parse() calls for PHP4 compatibility.

**Rev 1.0.7**  2009-Jan-03

* Ported from file-based cache to wp-database cache to eliminate file-permissions errors.

**Rev 1.0.6**  2009-Jan-01

* Added 2 div classes "dandyIDSidebarIdentities" and "dandyIDSidebarPoweredBy" to enable external css styling. Thanks [@dtownsend](http://twitter.com/dtownsend). 

**Rev 1.0.5**  2009-Jan-01

* Suppress PHP error "failed to open stream: Permission denied" when creation of cache file fails.

**Rev 1.0.4**  2008-Dec-31

* Updated class.dandyid.php to increase PHP cross-platform compatibility.

**Rev 1.0.3**  2008-Dec-30

* Added ability for Plugin to gracefully handle DandyID host server down.
* Added ability for Plugin to gracefully handle DandyID API url unavailable.

**Rev 1.0.2**  2008-Dec-28

* Added ability to Show-Favicons-and-Text-Links or Show-Favicons-only.

**Rev 1.0.1**  2008-Dec-22

* Initial revision.
