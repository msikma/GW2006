<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

// Note: this file should not import any other files, as it's being included very early on.
// It's designed to be able to run without any of the other theme code being present.
// See the index.php file in root.

/**
 * Scheduled task that locks old topics.
 * 
 * The bulk of the work is done in lock_old_topics().
 */
function scheduled__gw_auto_lock_old_topics($get_settings = false) {
  global $modSettings;

  if ($get_settings) {
    return [
      // Run once a week.
      'interval' => [1, 'w'],
    ];
  }
  
  // Amount of days of no replies after which a topic is locked due to age.
  $days = $modSettings['oldTopicDays'];

  // Only update topics that are currently unlocked.
  $old_status = 0;

  // Set the lock value to "2" (instead of the normal "1").
  // This is used to display auto locked topics differently from manually locked topics.
  $new_status = 2;

  return _lock_old_topics($days, $old_status, $new_status);
}

/**
 * Ensures that all GW scheduled tasks are in the database.
 * 
 * Note: in order for these tasks to activate, this file needs to be loaded from index.php.
 * This is done through a patch adding a require() to index.php, before the regular SMF
 * scheduled tasks are included.
 */
function install_theme_tasks() {
  global $db_prefix, $smcFunc;
  $registered_tasks = get_all_theme_registered_tasks();
  $defined_tasks = get_all_theme_scheduled_tasks();
  foreach ($defined_tasks as $task_name => $task) {
    if (!isset($registered_tasks[$task_name])) {
      // The task does not exist, so insert it into the database now.
      $settings = call_user_func_array($task, [true]);
      $request = $smcFunc['db_query']('', '
        insert into {db_prefix}scheduled_tasks
        (id_task, next_time, time_offset, time_regularity, time_unit, disabled, task)
        values
        (null, 0, 0, {int:regularity}, {string:unit}, 0, {string:task_name})
      ',
        [
          'regularity' => $settings['interval'][0],
          'unit' => $settings['interval'][1],
          'task_name' => $task_name,
        ]
      );
    }
  }
}

/**
 * Removes all GW scheduled tasks from the database.
 */
function remove_theme_tasks() {
  global $db_prefix, $smcFunc;
  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}scheduled_tasks
    where task like {string:task_prefix}
  ',
    [
      'task_prefix' => '_gw%'
    ]
  );
}

/**
 * Returns whether the theme's scheduled tasks are installed or not.
 */
function get_tasks_installation_status() {
  $status = [
    'all_tasks_installed' => true,
    'tasks' => [],
  ];

  $registered_tasks = get_all_theme_registered_tasks();
  $defined_tasks = get_all_theme_scheduled_tasks();
  foreach ($defined_tasks as $task_name => $task) {
    $is_installed = isset($registered_tasks[$task_name]);
    $status['tasks'][$task_name] = $is_installed;
    if (!$is_installed) {
      $status['all_tasks_installed'] = false;
    }
  }

  return $status;
}

/**
 * Returns all theme scheduled tasks that have been registered in the database.
 */
function get_all_theme_registered_tasks() {
  global $db_prefix, $smcFunc;
  
  $request = $smcFunc['db_query']('', '
    select id_task, disabled, task from {db_prefix}scheduled_tasks
    where task like {string:task_prefix}
  ',
    [
      'task_prefix' => '_gw%'
    ]
  );

  $registered_tasks = [];
  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $registered_tasks[$row['task']] = $row;
  }
  return $registered_tasks;
}

/**
 * List all theme tasks that have been defined.
 */
function get_all_theme_scheduled_tasks() {
  $defined_fns = get_defined_functions();
  $defined_task_fns = array_filter($defined_fns['user'], fn($item) => str_starts_with($item, 'scheduled__gw'));
  $defined_tasks = [];
  foreach ($defined_task_fns as $fn) {
    $task_name = substr($fn, 10);
    $defined_tasks[$task_name] = $fn;
  }
  return $defined_tasks;
}

/**
 * Locks all topics older than a given number of days.
 * 
 * Topics that are locked should be set to "2", to indicate they are auto locked.
 */
function _lock_old_topics($days, $old_status, $new_status) {
  global $db_prefix, $smcFunc;

  $request = $smcFunc['db_query']('', '
    update {db_prefix}topics t
    join {db_prefix}messages m on m.id_msg = t.id_last_msg
    set t.locked = {int:to_locked}
    where m.poster_time < unix_timestamp(date_sub(now(), interval {int:days} day))
    and t.locked = {int:locked}
  ',
    [
      'days' => $days,
      'to_locked' => $new_status,
      'locked' => $old_status
    ]
  );

  return !!$request;
}
