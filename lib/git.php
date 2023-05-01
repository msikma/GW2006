<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/context.php');
require_once('lib/cache.php');

/**
 * Returns the repository base URL from the composer.json file.
 */
function get_repo_base() {
  $composer = json_decode(file_get_contents(get_theme_dir().'/composer.json'), true);
  return $composer['repository'];
}

/**
 * Returns basic information about the current state of the Git repo.
 * 
 * The data is cached for 10 minutes as retrieving Git info is expensive.
 */
function get_git_info($refresh = false) {
  $key = 'gw2006_git_info';

  // Retrieve cached data from the database.
  if (!$refresh) {
    $cache = get_cached_data($key);
  }

  // If our data is outdated, get up-to-date information and store it.
  if ($refresh || $cache['not_found'] || $cache['is_stale']) {
    $data = _get_live_git_info();
    set_cached_data($key, $data, 600);
    return $data;
  }

  return $cache['data'];
}

/**
 * Returns basic information about the current state of the Git repo.
 */
function _get_live_git_info() {
  $theme_dir = get_theme_dir();
  $cmd_base = 'git --git-dir '.escapeshellarg("{$theme_dir}/.git");

  $hash = exec("{$cmd_base} rev-parse HEAD");
  $hash_short = substr($hash, 0, 7);
  $timestamp = exec("{$cmd_base} log -1 --format=%ct HEAD");
  $branch = exec("{$cmd_base} rev-parse --abbrev-ref --short HEAD");
  $count = exec("{$cmd_base} rev-list --count HEAD");

  // Whether the current branch is detached; i.e. the commit name is a hash.
  $formatted = format_git_version($branch, $count, $hash_short);

  // Get the repository base URL from the composer file.
  $repo_base = get_repo_base();

  return [
    'hash' => $hash,
    'hash_short' => $hash_short,
    'timestamp' => intval($timestamp),
    'branch' => $branch,
    'count' => intval($count),
    'formatted' => $formatted,
    'commit_url' => $repo_base.'/commit/'.$hash,
  ];
}

/**
 * Returns a formatted string representing the current git repo version.
 */
function format_git_version($branch, $count, $hash_short) {
  return "$branch-$count [$hash_short]";
}
