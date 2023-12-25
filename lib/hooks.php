<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

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
  $captcha_id = $_POST['gw_captcha_id'];
  $captcha_input = $_POST['gw_captcha'];
  // ob_start();
  // var_dump([$captcha_id, $captcha_input]);
  // $content = ob_get_clean();
  // $reg_errors['captcha_error'] = ['done', '<pre>'.$content.'</pre>', false];
}

/**
 * Installs our theme's custom hooks.
 */
function install_theme_hooks() {
  // Uses the non-standard "integrate_register_errors" hook that we have to patch in.
  // See <util/patch_user_scheduled_tasks.patch>.
  add_integration_function('integrate_register_errors', 'gw_verify_theme_captcha', true);
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
