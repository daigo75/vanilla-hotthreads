<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Constants used by HotThreads Plugin.
 */

define('HOTTHREADS_PAGESET_ALL', 'all');
define('HOTTHREADS_PAGESET_DISCUSSIONS', 'discussions');
define('HOTTHREADS_PAGESET_ANNOUNCEMENTS', 'announcements');

// Default Configuration Settings

// Default number of hot threads to display
define('HOTTHREADS_DEFAULT_MAXENTRIES', 10);
// Default Views Threshold. A Discussion will be considered "hot" if it received
// at least this amount of views
define('HOTTHREADS_DEFAULT_VIEWSTHRESHOLD', 100);
// Default Comments Threshold. A Discussion will be considered "hot" if it
// received at least this amount of comments
define('HOTTHREADS_DEFAULT_COMMENTSTHRESHOLD', 10);
// Default delay for the automatic updating of the Hot Threads list, in seconds
define('HOTTHREADS_DEFAULT_AUTOUPDATEDELAY', 120);

// Paths
define('HOTTHREADS_PLUGIN_PATH', PATH_PLUGINS . '/HotThreads');
define('HOTTHREADS_PLUGIN_LIB_PATH', HOTTHREADS_PLUGIN_PATH . '/lib');
define('HOTTHREADS_PLUGIN_CLASSES_PATH', HOTTHREADS_PLUGIN_LIB_PATH . '/classes');
define('HOTTHREADS_PLUGIN_MODULES_PATH', HOTTHREADS_PLUGIN_CLASSES_PATH . '/modules');

define('HOTTHREADS_PLUGIN_VIEWS_PATH', HOTTHREADS_PLUGIN_PATH . '/views');

// URLs
define('HOTTHREADS_PLUGIN_BASE_URL', '/plugin/hotthreads');
define('HOTTHREADS_GENERALSETTINGS_URL', HOTTHREADS_PLUGIN_BASE_URL . '/settings');
define('HOTTHREADS_PAGE_URL', '/discussions/hotthreads');

// Return Codes
define('HOTTHREADS_OK', 0);
