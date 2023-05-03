<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/context.php');
require_once('lib/cache.php');

/**
 * Returns the Composer package data.
 */
function get_package_data() {
  $composer = json_decode(file_get_contents(get_theme_dir().'/composer.json'), true);
  return $composer;
}

/**
 * Returns basic information about the project from its package.
 */
function get_package_version() {
  $composer = get_package_data();
  return [
    'name' => $composer['name'],
    'version' => $composer['version'],
    'repository' => $composer['repository'],
    'homepage' => $composer['homepage'],
    'support' => $composer['support'],
  ];
}

/**
 * Returns the repository base URL from the composer.json file.
 */
function get_repo_base() {
  $composer = get_package_data();
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
 * 
 * Commit history is limited to the last 25 commits.
 */
function _get_live_git_info() {
  $theme_dir = get_theme_dir();
  $cmd_base = 'git --git-dir '.escapeshellarg("{$theme_dir}/.git");

  $hash = exec("{$cmd_base} rev-parse HEAD");
  $hash_short = substr($hash, 0, 7);
  $timestamp = exec("{$cmd_base} log -1 --format=%ct HEAD");
  $branch = exec("{$cmd_base} rev-parse --abbrev-ref --short HEAD");
  $count = exec("{$cmd_base} rev-list --count HEAD");
  exec("{$cmd_base} --no-pager log --pretty=format:\"%H %ct %s\" --no-color --no-abbrev -25", $history);

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
    'history' => parse_git_history($repo_base, $history),
    'repo_url' => $repo_base,
    'package' => get_package_version(),
    'commit_url' => get_commit_url($repo_base, $hash),
  ];
}

/**
 * Parses the most recent Git commit lines from git log and returns them as an array.
 */
function parse_git_history($repo_base, $history_lines) {
  $history = [];

  foreach ($history_lines as $line) {
    $data = explode(' ', $line, 3);
    $history[] = [
      'hash' => $data[0],
      'hash_short' => substr($data[0], 0, 7),
      'commit_url' => get_commit_url($repo_base, $data[0]),
      'timestamp' => intval($data[1]),
      'subject' => $data[2],
    ];
  }

  return $history;
}

/**
 * Returns a URL to a given commit by its full hash.
 */
function get_commit_url($repo_base, $hash) {
  return $repo_base.'/commit/'.$hash;
}

/**
 * Returns a formatted string representing the current git repo version.
 */
function format_git_version($branch, $count, $hash_short) {
  return "$branch-$count [$hash_short]";
}
