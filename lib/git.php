<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Returns the repository base URL from the composer.json file.
 */
function get_repo_base() {
  global $settings;

  $composer = json_decode(file_get_contents($settings['theme_dir'].'/composer.json'), true);
  return $composer['repository'];
}

/**
 * Returns basic information about the current state of the Git repo.
 */
function get_git_info($default_branch = 'develop') {
  global $settings;
  
  // References to the relevant .git files.
  $git = $settings['theme_dir'].'/.git/';
  $head = 'HEAD';
  $file_head = $git.'/'.$head;
  $file_logs = $git.'/logs/'.$head;

  // Extract the current Git state from the .git directory.
  $head = explode('/', substr(trim(file_get_contents($file_head)), 5));
  $logs = explode("\n", trim(file_get_contents($file_logs)));
  $hash = trim(file_get_contents($git.implode('/', $head)));
  $hash_short = substr($hash, 0, 7);
  $branch = end($head);
  
  // Extract the timestamp from the commit log (e.g. "1671303806 +0100").
  preg_match('/([0-9]{10}) [+-][0-9]{4}/', end($logs), $ts);
  $date = date('Y-m-d', $ts[1]);

  // Get the repository base URL from the composer file.
  $repo_base = get_repo_base();

  return [
    'hash' => $hash,
    'ts' => $ts[0],
    'date' => $date,
    'hash_short' => $hash_short,
    'branch' => $branch,
    'count' => count($logs),
    'formatted' => $branch.'-'.count($logs).' ['.$hash_short.']',
    'commit_url' => $repo_base.'/commit/'.$hash,
    'is_default_branch' => $branch === $default_branch,
  ];
}
