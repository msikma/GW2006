<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/captcha.php');
require_once('lib/db.php');

global $theme_hooks;

// All hooks that are a part of this theme.
$theme_hooks = [
  'integrate_register_errors' => ['gw_verify_theme_captcha'],
];

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
 * Installs our theme's custom hooks.
 */
function install_theme_hooks() {
  // Uses the non-standard "integrate_register_errors" hook that we have to patch in.
  // See <util/patch_user_scheduled_tasks.patch>.
  add_integration_function('integrate_register_errors', 'gw_verify_theme_captcha', true);

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
