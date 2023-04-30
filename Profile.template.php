<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// Displayed on profile pages after the top menu but before the main page content.
function template_profile_above() {
  return render_template('pages/profile/above.twig');
}

// Displayed on profile pages after the main page content.
function template_profile_below() {
  // Nothing here.
}

// Displays the user's profile (uneditable).
// <http://vesuvius.local/gw/index.php?action=profile;u=1>
// <http://vesuvius.local/gw/index.php?action=profile;area=summary;u=1>
function template_summary() {
  return render_template('pages/profile/summary.twig');
}

// Shows all the posts, topics or attachments of the user in chronological order.
// <http://vesuvius.local/gw/index.php?action=profile;area=showposts;sa=messages;u=1>
// <http://vesuvius.local/gw/index.php?action=profile;area=showposts;sa=topics;u=1>
// <http://vesuvius.local/gw/index.php?action=profile;area=showposts;sa=attach;u=1>
function template_showPosts() {
  global $modSettings;
  return render_template('pages/profile/form_posts.twig', ['page_index_context' => get_page_index_context($modSettings['defaultMaxMessages'])]);
}

// Displays a user's activity and shows what error messages were generated by them.
// <http://vesuvius.local/gw/index.php?action=profile;area=tracking;sa=activity;u=1>
function template_trackActivity() {
  return render_template('pages/profile/form_track_activity.twig');
}

// Allows the admin to track the user's IP and do lookups.
// <http://vesuvius.local/gw/index.php?action=profile;area=tracking;sa=ip;u=1>
function template_trackIP() {
  return render_template('pages/profile/form_track_ip.twig');
}

// Shows what permissions a user has, both in general and per forum.
// <http://vesuvius.local/gw/index.php?action=profile;area=permissions;u=1>
function template_showPermissions() {
  return render_template('pages/profile/form_permissions.twig');
}

// Shows user statistics, including a bar graph for activity by hour of the day.
// <http://vesuvius.local/gw/index.php?action=profile;area=statistics;u=1>
function template_statPanel() {
  return render_template('pages/profile/form_statistics.twig');
}

// Allows a member to delete their account, or an admin to delete another user's account (and posts/topics).
// <http://vesuvius.local/gw/index.php?action=profile;area=deleteaccount;u=1>
function template_deleteAccount() {
  return render_template('pages/profile/form_delete_account.twig');
}

// Template for showing all the buddies of the current user.
// <http://vesuvius.local/gw/index.php?action=profile;area=lists;sa=buddies;u=1>
function template_editBuddies() {
  return render_template('pages/profile/form_buddies.twig');
}

// Template for showing the ignore list of the current user.
// <http://vesuvius.local/gw/index.php?action=profile;area=lists;sa=ignore;u=1>
function template_editIgnoreList() {
  return render_template('pages/profile/form_ignore.twig');
}

// Template for editing profile options.
function template_edit_options() {
  return render_template('pages/profile/form_edit.twig', ['setting_groups' => get_current_settings_group(), 'use_groups' => true]);
}

// Personal Message settings.
function template_profile_pm_settings() {
  return render_template('components/profile/fields_pm_settings.twig');
}

// Prints the profile fields for the theme settings.
// <http://vesuvius.local/gw/index.php?action=profile;u=1;area=theme>
function template_profile_theme_settings() {
  return render_template('components/profile/fields_theme_settings.twig');
}

// Template for changing notification settings.
// <http://vesuvius.local/gw/index.php?action=profile;area=notification;u=1>
function template_notification() {
  return render_template('pages/profile/form_notification.twig');
}

// Template for choosing group membership. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=profile;area=groupmembership;u=1>
function template_groupMembership() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Group membership selection is not implemented yet.']);
}

// Template for ignoring specific forums. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=profile;area=ignoreboards;u=1>
function template_ignoreboards() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Board ignore is not implemented yet.']);
}

// Loads a number of common variables for the warning system. FIXME_NOT_IMPLEMENTED
function template_load_warning_variables() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Warning system is not implemented yet.']);
}

// Show all of a user's warnings. FIXME_NOT_IMPLEMENTED
function template_viewWarning() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Warning system is not implemented yet.']);
}

// Template for issuing warnings. FIXME_NOT_IMPLEMENTED
function template_issueWarning() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Warning system is not implemented yet.']);
}

// Display a load of drop down selectors for allowing the user to change group.
function template_profile_group_manage() {
  return render_template('components/profile/fields_membergroup.twig');
}

// Callback function for entering a birthdate.
function template_profile_birthdate() {
  return render_template('components/profile/fields_birthdate.twig');
}

// Show the signature editing box.
function template_profile_signature_modify() {
  return render_template('components/profile/fields_signature.twig');
}

function template_profile_avatar_select() {
  return render_template('components/profile/fields_avatar_select.twig');
}

// Callback for modifying karma. FIXME_NOT_IMPLEMENTED
function template_profile_karma_modify() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'Warning system is not implemented yet.']);
}

// Select the time format!
function template_profile_timeformat_modify() {
  return render_template('components/profile/fields_timeformat_modify.twig');
}

// Time offset?
function template_profile_timeoffset_modify() {
  return render_template('components/profile/fields_timeoffset_modify.twig');
}

// Theme?
function template_profile_theme_pick() {
  return render_template('components/profile/fields_theme_pick.twig');
}

// Smiley set picker. FIXME add link.
function template_profile_smiley_pick() {
  return render_template('components/profile/fields_smiley_pick.twig');
}

// Change the way a user logs in to the forum.
function template_authentication_method() {
  return render_template('components/profile/fields_authentication_method.twig');
}
