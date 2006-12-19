<?php

/**
 *
 * License information:
 * This program may be distributed under the terms of this General Public License.
 * the whole license can be seen in gpl.txt or at http://www.gnu.org/copyleft/gpl.html
 *
*/

// general config
define('KB_TITLE', '{$title}');
define('KB_HOST', '{$host}');
define('KB_SITE', '{$site}');

// Main website, leave blank if killboard is by itself
define('MAIN_SITE', '');

// corporation OR alliance id
define('CORP_ID', {$cid});
define('ALLIANCE_ID', {$aid});

// admin password
define('ADMIN_PASSWORD', '{$adminpw}');
define('SUPERADMIN_PASSWORD', '');

// debug, shows page time generation
define('KB_PROFILE', 0);

// page cache
define('KB_CACHE', 0);
define('KB_CACHEDIR', 'cache/data');

$cacheignore = array(
    'home',
    'pilot_detail',
    'kill_detail',
    'admin',
    'admin_cc',
    'admin_rental',
    'igb',
    'post_igb',
    'portrait_grab');

$cachetimes = array (
    'awards' => 1440,
    'campaigns' => 30,
    'contracts' => 30,
    'corp_detail' => 60,
    'alliance_detail' => 240);

define('STYLE_URL', '{$style}');
define('COMMON_URL', '{$common}');
define('IMG_URL', '{$img}');

define('DB_HALTONERROR', true);

// database config
define('DB_HOST', '{$dbhost}');
define('DB_NAME', '{$db}');
define('DB_USER', '{$user}');
define('DB_PASS', '{$pass}');

// please make sure that there is no space behind the closing tag
?>