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
  $tasks = get_tasks_installation_status();
  $hooks = get_hooks_installation_status();

  return [
    'all_prerequisites_installed' => $tasks['all_tasks_installed'] && $hooks['all_hooks_installed'],
    'tasks' => $tasks['tasks'],
    'hooks' => $hooks['hooks'],
  ];
}

/**
 * Removes all custom theme prerequisites.
 */
function remove_theme_prerequisites() {
  remove_theme_hooks();
  remove_theme_tasks();
}

/**
 * Installs all custom theme prerequisites.
 */
function install_theme_prerequisites() {
  install_theme_hooks();
  install_theme_tasks();
}
