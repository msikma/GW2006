<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/captcha.php');
require_once('lib/db.php');

global $theme_hooks;

// All hooks that are a part of this theme.
$theme_hooks = [
  'integrate_register_errors' => ['gw_verify_theme_captcha'],
  'integrate_load_theme' => ['gw_delete_spambot_posts_after_ban'],
  'integrate_register' => ['gw_alter_member_during_register'],
];

/**
* Modifies the user object right before registering.
*/
function gw_alter_member_during_register(&$regOptions, $theme_vars) {
  // Default new users to the thread-based PM option.
  $regOptions['register_vars']['pm_prefs'] = 2;
}

/**
 * Checks a theme captcha and rejects the user's registration attempt if invalid.
 * 
 * This must be registered to the non-standard "integrate_register_errors" hook.
 */
function gw_verify_theme_captcha(&$regOptions, &$reg_errors) {
  // The captcha input field is not passed on through the registration options.
  list($result, $reason) = verify_captcha_challenge($_POST['gw_captcha_id'], $_POST['gw_captcha_value']);

  if ($result === false) {
    switch ($reason) {
      case 'not_found':
      case 'already_solved':
        $explanation = 'Sorry, you appear to be attempting to solve an old captcha. Please try again.';
        break;
      case 'incorrect':
        $explanation = 'Sorry, your captcha solution was incorrect. Please try again.';
        break;
      default:
        $explanation = 'An unknown error occurred. Please try again later.';
        break;
    }
    $reg_errors['captcha_error'] = ['done', $explanation, false];
  }
}

/**
 * Allows for a banned spambot's posts to be removed immediately after adding the ban.
 */
function gw_delete_spambot_posts_after_ban() {
  global $context;

  // Do nothing if the user does not have permission to perform moderation actions.
  if (empty($context['user']) || $context['user']['can_mod'] !== true) {
    return;
  }

  // Ensure we only run this once per request.
  if (isset($GLOBALS['gw_delete_spambots_request'])) {
    return;
  }
  $GLOBALS['gw_delete_spambots_request'] = true;

  // Check if this request follows a member ban.
  $is_ban_request = !empty($_POST['ban_group']) && intval($_POST['ban_post_delete_posts']) === 1;
  if (!$is_ban_request) {
    return;
  }

  $ban_suggestions = [
    'ip' => $_POST['main_ip'],
    'email' => $_POST['email'],
    'id' => $_POST['bannedUser'],
  ];
  
  // Get a list of topics and posts that we can either delete or wipe.
  $deletable_posts = get_banned_member_deletable_posts($ban_suggestions, ['id']);
  
  // Now we can begin deleting and wiping topics and posts.
  // Deleting topics is straightforward, and we delete all messages in the topic as well.
  delete_spam_topics($deletable_posts['delete_topics']);
  delete_spam_posts($deletable_posts['delete_posts']);
  // Wiping messages is a different story: these are messages that we can't fully delete,
  // mostly because members may have made response posts to them already and it'd be confusing
  // if they vanished, so instead what we do is clear them of identifying information.
  wipe_spam_posts($deletable_posts['wipe_posts']);
  if ($ban_suggestions['id']) {
    wipe_spam_members([$ban_suggestions['id']]);
  }
}

/**
 * Installs our theme's custom hooks.
 */
function install_theme_hooks() {
  // Uses the non-standard "integrate_register_errors" hook that we have to patch in.
  // See <util/patch_user_scheduled_tasks.patch>.
  add_integration_function('integrate_register_errors', 'gw_verify_theme_captcha', true);

  // Allows us to delete a user's posts when banning them as a spambot.
  add_integration_function('integrate_load_theme', 'gw_delete_spambot_posts_after_ban', true);

  // Sets a number of member preferences to our defaults.
  add_integration_function('integrate_register', 'gw_alter_member_during_register', true);

  // In order to generate captchas we need to ensure that the captcha database table exists.
  if (!_has_captcha_table()) {
    _create_captcha_table();
  }
}

/**
 * Removes our theme's custom hooks.
 */
function remove_theme_hooks() {
  remove_integration_function('integrate_register_errors', 'gw_verify_theme_captcha');
  remove_integration_function('integrate_load_theme', 'gw_delete_spambot_posts_after_ban');
}

/**
 * Returns whether theme integration hooks are installed or not.
 */
function get_hooks_installation_status() {
  global $modSettings, $theme_hooks;

  $status = [
    'all_hooks_installed' => true,
    'hooks' => [],
  ];

  // All hooks currently registered.
  $registered = [];
  foreach (array_keys($theme_hooks) as $hook) {
    $registered[$hook] = @array_filter(explode(',', $modSettings[$hook]));
  }
  foreach ($theme_hooks as $hook => $fns) {
    foreach ($fns as $fn) {
      $is_installed = in_array($fn, $registered[$hook]);
      $status['hooks'][$fn] = $is_installed;
      if (!$is_installed) {
        $status['all_hooks_installed'] = false;
      }
    }
  }
  
  return $status;
}
