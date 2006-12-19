        //////
//////  //////
//////  liq's feed syndication mod v1.3
////////////
////////////
////////////
////////////



//INSTALLATION:

1. create directory mods/feed
2. put the files settings.php and feed_fetcher.php in /mods/feed_syndication directory.
3. put feed.php in your /common directory
3. login as admin and enable the feed-mod in 'Mods'
4. wee. youre done.

additionally you can add the following line to common/admin_menu.php for direct access:
$menubox->addOption( "Feeds", "?a=settings_feed_syndication" );
or if you use eve-dev kilboard version 1.2.0 and above (using smarty templates):
$menubox->addOption("link", "Feeds", "?a=settings_feed_syndication");



//FILES:

feed.php <- prints out all kills of the actual week in rss format.

feed_fetcher.php <- contains the actual xml parser class.

admin_feed.php <- is the interface to feed_fetcher.php. lets you define feeds of other killboards and import kills.



//NOTES:

-> enter feed urls in the format: http://killboard.eve-d2.com/?a=feed

-> a list of known feeds can be found here: http://www.eve-dev.net/e107_plugins/forum/forum_viewtopic.php?1896

-> clicking the 'fetch' button will get all kills of the actual week.

-> ticking the 'grab-ALL-mails-box' will grab the kills of ALL weeks (iteration from week #1 through to week #52)!
** be careful with this option! it can take several minutes or even hours to have all the kills parsed and added to your database **
** be patient. don't close your browser, don't reload. you only need to use this ONCE to initialize a feed connection.
after initializing, weekly updates are totally sufficient to keep your board uptodate **

-> by default their kills aka. your losses get fetched unless you tick the 'get kills'
option in which case their losses aka. your kills get fetched

-> GZip compression is enabled by default because the board automatically
checks and decides if a fetched stream supports GZip compression - if not it uses regular html/rss output.

-> if you are running a master killboard (eg. to supply and share killdata with other killboards) edit settings.php and set 'MASTER' to 1.
the board will then even fetch kills not related to the alliance or corp ID you set in the killboards config.php.
leave this option untouched if youre running a normal killboard for your corp or alliance - it will just slow things down.

-> the feed is not supposed to be human readable so ffs dont complain about the formatting,
for human readable rss output use the rss mod ( /?a=rss ) instead.



//VERSION CHANGES:

//v1.3

feed.php:
- added support for GZip compressed output
- minor code cleanups

feed_fetcher.php:
- converted to a mod
- added support for getting GZip compressed feeds and a fallback if the feed is uncompressed
- changed the text in auto-comment to only show the remote killboards url and not the 
complete path with all passed variables, making it more readable

admin_feed.php / settings.php
- converted to a mod 
- renamed to settings.php
- added option to fetch streams with Gzip compression - enabled by default
- added support for master killboards whose only purpose is to collect and share kill data, see notes


//v1.2

feed.php:
- added output of feed's version number

feed_fetcher.php:
- none

admin_feed.php
- rearranged options to be inline with other admin settings pages
- added checkboxes to enable / disable fetching specific feed urls


//v1.1

feed.php:
- now lets you also grab losses, not only kills.
- will give out only the kills where the request-board-owner (corp or alliance) is victim,
or in case of losses, the involved. this should speed things up significantly.
- included a check for Last_Kill_ID so it starts to output kill data only after
that ID to remove redundancy when fetching a feed more than once.

feed_fetcher.php:
- included getConfig and setConfig call functions to guarantee backwards compatiblity with older killboard version that miss those functions.
- included calls to the killboards comment function so you can add predefined comments to autoparsed kills aka. "post your losses noob".

admin_feed.php:
- made the number of feeds user selectable
- more detailed descriptions
- option for verbose mode (lets you show/hide errormessages from imported killmails)
- option to automatically enter a comment with the autoparsed kill
- option to get kills instead of losses
- added backwards compatibility to exi's v1.0 mod



cheers
//liquidism
