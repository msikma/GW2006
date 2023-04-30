<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License
//
// gen_emoticons_sql.php <https://localhost/gw/Themes/gw2006/util/gen_emoticons_sql.php>
// Script for generating an SQL dump that inserts the emoticons.

$emoticons_base_path = '../images/__gwnew/emoticons';
$emoticons_info_path = realpath("{$emoticons_base_path}").'/info.json';
$emoticons = json_decode(file_get_contents($emoticons_info_path), true);

$timestamp = date('Y-m-d\TH:i:s.Z\Z');
$counter = 0;
$db_prefix = $_GET['db_prefix'] ?? 'smf_';
$path = @$_GET['path'];

if (empty($path)) {
  print("To run this script, add your database prefix and the path to your SMF installation to the query string, using \"db_prefix\" and \"path\".");
  print("\n\nE.g. gen_emoticons_sql.php?db_prefix=smf_&path=/path/to/install");
  die();
}

$loc_regular = 0;
$loc_hidden = 1;
$loc_popup = 2;

$files = [];

$buffer = [];
$buffer[] = "-- GW2006 emoticon import script";
$buffer[] = "-- emoticon set name: {$emoticons['name']} <{$emoticons['website']}>";
$buffer[] = "-- timestamp: {$timestamp}";
$buffer[] = "";
$buffer[] = "truncate `{$db_prefix}smileys`;";
$buffer[] = "";
foreach ($emoticons['sets'] as $set) {
  $buffer[] = "-- set: \"{$set['name']}\"".(!empty($set['title']) ? " - {$set['title']}" : "");
  $buffer[] = "";
  foreach ($set['items'] as $emoticon) {
    $description = $emoticon['description'] ?? ucfirst(pathinfo($emoticon['filename'], PATHINFO_FILENAME));
    $location = $emoticon['hidden'] ? $loc_hidden : ($emoticon['location'] === 'postform' ? $loc_regular : $loc_popup);
    $emoticon_path = $set['name'].'/'.$emoticon['filename'];
    if (isset($files[$emoticon['filename']])) {
      print("Error: two or more emoticons have the same filename.");
      print("");
      print("\"{$files[$emoticon['filename']]}\" and \"{$emoticon_path}\".");
      exit;
    }
    $files[$emoticon['filename']] = $emoticon_path;
    for ($n = 0; $n < count($emoticon['code']); ++$n) {
      $code = is_array($emoticon['code'][$n]) ? implode('', array_map('chr', $emoticon['code'][$n])) : $emoticon['code'][$n];
      $code_location = $n === 0 ? $location : $loc_hidden;
      $buffer[] = "insert into `{$db_prefix}smileys` (`code`, `filename`, `description`, `smiley_order`, `hidden`) values ('{$code}', '{$emoticon['filename']}', '{$description}', '{$counter}', '{$code_location}');";
      $counter += 1;
    }
  }
  $buffer[] = "";
}
$buffer[] = "-- end of emoticons.";
$buffer[] = "";
$buffer[] = "-- copy over the files to the set directory:";
$buffer[] = "--";

$buffer[] = "-- rm \"{$path}/Smileys/default/\"*";
foreach ($files as $file) {
  $buffer[] = "-- cp \"{$emoticons_base_path}/{$file}\" \"{$path}/Smileys/default/\"";
}

$buffer[] = "-- echo -en \"<?php\\nif (file_exists(dirname(dirname(__FILE__)) . '/index.php')) {\\n\\tinclude(dirname(dirname(__FILE__)) . '/index.php');\\n}\\nelse exit;\" > \"{$path}/Smileys/default/index.php\"";
$buffer[] = "";
$buffer[] = "-- end of script.";
$buffer[] = "";
$buffer[] = "";

header('Content-Type: text/plain');
print(implode("\n", $buffer));
