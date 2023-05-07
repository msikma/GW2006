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

  // Check if this is a force refresh request.
  $refresh = is_changelog_refresh_request() || $refresh;

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
  exec("{$cmd_base} --no-pager log --pretty=format:\"%H %ct %s\" --no-color --no-abbrev -25 --numstat", $history);

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
    'history' => parse_git_history($repo_base, $history, $count),
    'repo_url' => $repo_base,
    'package' => get_package_version(),
    'commit_url' => get_commit_url($repo_base, $hash),
  ];
}

/**
 * Parses the most recent Git commit lines from git log and returns them as an array.
 */
function parse_git_history($repo_base, $history_lines, $start_count) {
  $history_lines[] = '';
  $history = [];

  $item = [];
  $type = 1;
  foreach ($history_lines as $line) {
    if ($type === 1) {
      // The first line in an item contains base information about the commit.
      $item = parse_commit_line($line, count($history), $start_count, $repo_base);
      $type = 2;
      continue;
    }
    if ($type === 2) {
      // When we reach an empty line, conclude this item.
      if (trim($line) === '' && !empty($item)) {
        $item = array_merge($item, ['summary' => get_commit_short_summary($item)]);
        unset($item['changes']);
        $history[] = $item;
        $item = [];
        $type = 1;
        continue;
      }

      // If not, assume this line contains --numstat information.
      $item['changes'][] = parse_commit_file_line($line);
      continue;
    }
  }

  return $history;
}

/**
 * Parses a Git commit line as defined by our git log command.
 */
function parse_commit_line($line, $count, $start_count, $repo_base) {
  $data = explode(' ', $line, 3);
  $item = [
    'hash' => $data[0],
    'hash_short' => substr($data[0], 0, 7),
    'commit_url' => get_commit_url($repo_base, $data[0]),
    'timestamp' => intval($data[1]),
    'subject' => $data[2],
    'n' => $start_count - $count,
    'changes' => [],
  ];
  return $item;
}

/**
 * Parses a Git insertions/deletions line.
 */
function parse_commit_file_line($line) {
  $data = preg_split("/\t+/", $line);
  $item = [
    'insertions' => $data[0] === '-' ? null : intval($data[0]),
    'deletions' => $data[1] === '-' ? null : intval($data[1]),
    'filename' => trim($data[2]),
  ];
  return $item;
}

/**
 * Returns a shorthand format for the number of insertions/deletions for a commit.
 */
function get_commit_short_summary($commit_data) {
  $changes = get_commit_changes($commit_data);
  return ['+'.$changes['insertions'], '-'.$changes['deletions']];
}

/**
 * Returns the total number of insertions, deletions and changes for a commit.
 */
function get_commit_changes($commit_data) {
  $insertions = 0;
  $deletions = 0;
  $changes = 0;
  foreach ($commit_data['changes'] as $change) {
    $changes += 1;
    if (is_integer($change['insertions'])) {
      $insertions += $change['insertions'];
    }
    if (is_integer($change['deletions'])) {
      $deletions += $change['deletions'];
    }
  }
  return [
    'insertions' => $insertions,
    'deletions' => $deletions,
    'changes' => $changes,
  ];
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
