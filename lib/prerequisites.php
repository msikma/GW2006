<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/db.php');
require_once('lib/tasks.php');
require_once('lib/hooks.php');

/**
 * Returns the status of theme prerequisites.
 * 
 * This includes our custom hooks and tasks as well as various other things.
 */
function get_theme_prerequisites_status() {
  // Checks if our tasks/hooks have been installed.
  $tasks = get_tasks_installation_status();
  $hooks = get_hooks_installation_status();

  // Checks for whether other packages are installed.
  $has_ebg_groups = are_gw_ebg_groups_enabled();

  return [
    'all_prerequisites_installed' => $tasks['all_tasks_installed'] && $hooks['all_hooks_installed'] && $has_ebg_groups,
    'tasks' => $tasks['tasks'],
    'hooks' => $hooks['hooks'],
  ];
}

/**
 * Adds the Easy Ban Groups toggle to our internal ban groups.
 */
function enable_gw_ebg_groups() {
  return toggle_gw_ebg_groups(true);
}

/**
 * Removes the Easy Ban Groups toggle from our internal ban groups.
 */
function disable_gw_ebg_groups() {
  return toggle_gw_ebg_groups(false);
}

/**
 * Toggles whether our internal ban groups are set up to be used with Easy Ban Groups.
 */
function toggle_gw_ebg_groups($enabled) {
  global $db_prefix, $smcFunc;

  if (!is_ebg_installed()) {
    return;
  }
  
  $request = $smcFunc['db_query']('', '
    update {db_prefix}ban_groups
    set easy_bg = {int:easy_bg}
    where name in ({array_string:ban_group_name})
  ',
    [
      'ban_group_name' => ['Spam'],
      'easy_bg' => $enabled ? 1 : 0,
    ]
  );
}

/**
 * Checks whether our internal ban groups are set up for Easy Ban Groups.
 */
function are_gw_ebg_groups_enabled() {
  global $db_prefix, $smcFunc;

  if (!is_ebg_installed()) {
    return;
  }
  
  $request = $smcFunc['db_query']('', '
    select id_ban_group, easy_bg from {db_prefix}ban_groups
    where name in ({array_string:ban_group_name})
  ',
    [
      'ban_group_name' => ['Spam'],
    ]
  );
  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    if (intval($row['easy_bg']) === 0) {
      return false;
    }
  }
  return true;
}

/**
 * Checks whether the Easy Ban Groups package is installed.
 */
function is_ebg_installed() {
  // If the Easy Ban Groups package is installed, the ban_groups table will have an "easy_bg" column.
  return column_exists('ban_groups', 'easy_bg');
}

/**
 * Removes all custom theme prerequisites.
 */
function remove_theme_prerequisites() {
  remove_theme_hooks();
  remove_theme_tasks();
  disable_gw_ebg_groups();
}

/**
 * Installs all custom theme prerequisites.
 * 
 * This can safely be called multiple times; anything already installed is skipped.
 */
function install_theme_prerequisites() {
  install_theme_hooks();
  install_theme_tasks();
  enable_gw_ebg_groups();
}
