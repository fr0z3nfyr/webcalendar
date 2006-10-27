<?php
/*
 * $Id$
 *
 * Description:
 * This script is intended to be used inside an IFRAME on another website
 * It can be embedded like so
 * <iframe name="minical" frameborder="0" height="190" width="250"     src="http://cal/minical.php";> 
 *
 * You must have public access enabled in System Settings to use this
 * page (unless you modify the $public_must_be_enabled setting below
 * in this file).
 *
 * By default (if you do not edit this file), events for the public
 * calendar will be used
 *
 * Input parameters:
 * You can override settings by changing the URL parameters:
 *   - cat_id: specify a category id to filter on
 *   - user: login name of calendar to display (instead of public
 *     user), if allowed by System Settings.  
 *    Only NUC Calendar that are marked PUBLIC can be specified
 *
 * Security:
 * $PUBLISH_ENABLED must be set true
 * 
 */
include_once 'includes/init.php';

load_global_settings ();

//These values will be used by styles.php to customize the size of this calendar
$MINICALWIDTH = '160px';
$MINICALFONT = '11px';
$DISPLAY_WEEKENDS = true;

if ( empty ( $PUBLISH_ENABLED ) || $PUBLISH_ENABLED != 'Y' ) {
  header ( 'Content-Type: text/plain' );
  echo print_not_auth ();
  exit;
}
/*
 *
 * Configurable settings for this file.  You may change the settings
 * below to change the default settings.
 * These settings will likely move into the System Settings in the
 * web admin interface in a future release.
 *
 */
// The html target window to use when clicking on the minical
// You should be able to set this to the desired frame or window 
// to receive the results.
$MINI_TARGET = '_blank';

// Change this to false if you still want to access this page even
// though you do not have public access enabled.
$public_must_be_enabled = true;

// Login of calendar user to use
// '__public__' is the login name for the public user
$user = ( empty ( $user )?'__public__' : $user);


// Allow the URL to override the user setting such as
// "minical.php?user=_NUC_training"
//If false, __public_ will always be used
$allow_user_override = false;

// Load just a specified category (by its id)
// Leave blank to not filter on category (unless specified in URL)
// Can override in URL with "minical.php?cat_id=4"
$cat_id = ( empty ( $cat_id )?'' : $cat_id);

// End configurable settings...

// Set for use elsewhere as a global
$login = $user;



if ( $public_must_be_enabled && $PUBLIC_ACCESS != 'Y' ) {
  $error = print_not_auth () . '.';
}

if ( $allow_user_override ) {
  $u = getValue ( 'user', "[A-Za-z0-9_\.=@,\-]+", true );
  if ( ! empty ( $u ) ) {
    $user = $u;
    $login = $u;
    // We also set $login since some functions assume that it is set.
  }
}

load_user_preferences ();

user_load_variables ( $login, 'minical_' );

if ( $user != '__public__' && ! nonuser_load_variables ( $login, 'minica_' ) ) {
  die_miserable_death ( translate ( 'No such nonuser calendar' ) .
    ': ' . $login );
}

if ( $user != '__public__' && ( empty ( $minical_is_public ) || $minical_is_public != 'Y' ) ) {
  die_miserable_death ( translate ( 'This Calendar is not Public' ) );
}

$next = mktime ( 0, 0, 0, $thismonth + 1, 1, $thisyear );
$nextyear = date ( 'Y', $next );
$nextmonth = date ( 'm', $next );

$prev = mktime ( 0, 0, 0, $thismonth - 1, 1, $thisyear );
$prevyear = date ( 'Y', $prev );
$prevmonth = date ( 'm', $prev );

$boldDays = true;
$startdate = mktime ( 0, 0, 0, $thismonth, 1, $thisyear );
$enddate = mktime ( 23, 59, 59, $thismonth + 1, 0, $thisyear );


$HeadX = '';
if ( $AUTO_REFRESH == 'Y' && ! empty ( $AUTO_REFRESH_TIME ) ) {
  $refresh = $AUTO_REFRESH_TIME * 60; // convert to seconds
  $HeadX = "<meta http-equiv=\"refresh\" content=\"$refresh; url=minical.php?$u_url" .
    "year=$thisyear&amp;month=$thismonth  \" />\n";
}

$INC = '';
$BodyX = '';
//Don't display custom header
print_header($INC,$HeadX,$BodyX,true);

/* Pre-Load the repeated events for quicker access */
$repeated_events = read_repeated_events ( $user, $cat_id, $startdate, $enddate );

/* Pre-load the non-repeating events for quicker access */
$events = read_events ( $user, $startdate, $enddate, $cat_id );

echo display_small_month ( $thismonth, $thisyear, true, false );

//Reset...just in case
$login = '';
?>
</body>
</html>
