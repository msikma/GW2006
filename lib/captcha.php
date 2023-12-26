<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/db.php');

global $captcha_emoticons_amount, $captcha_available_items, $captcha_basedir, $captcha_cache_file;

// Number of emoticons we ask the user to match.
$captcha_emoticons_amount = 6;
// Number of matching emoticons that are available.
$captcha_available_items = 27;
// Base directory containing captcha images.
$captcha_basedir = __DIR__.'/../private/captcha';
// The cache file for base 64 encoded image data.
$captcha_cache_file = __DIR__.'/../cache/captcha_cache.json';

/**
 * Creates a new captcha challenge.
 * 
 * This returns all data needed to display the challenge to the user.
 */
function create_captcha_challenge() {
  global $captcha_cache_file;

  remove_old_captcha_challenges();
  $captcha = create_captcha_challenge_data();
  $images = get_captcha_image_data();

  $tags_solution = _generate_emoticon_tags($captcha['solution'], 'type1', false, $images);
  $tags_challenge = _generate_emoticon_tags($captcha['challenge'], 'type2', true, $images);
  
  return [
    'challenge_data' => $captcha,
    'challenge' => $tags_challenge,
    'solution' => $tags_solution,
  ];
}

/**
 * Creates the data for a new captcha challenge.
 * 
 * This only generates the challenge and the solution data, and inserts it into the database.
 */
function create_captcha_challenge_data() {
  global $db_prefix, $smcFunc, $user_info;
  global $captcha_emoticons_amount, $captcha_available_items;

  // Obtains a list of emoticons that the user needs to match.
  // We'll give the user twice as many emoticons to pick from.
  $range = shuffle_array_securely(range(1, $captcha_available_items));
  $challenge_range = array_slice($range, 0, $captcha_emoticons_amount * 2);
  $solution_range = shuffle_array_securely(array_slice(shuffle_array_securely($challenge_range), 0, $captcha_emoticons_amount));

  // Zero pad integers.
  array_walk($challenge_range, fn(&$item) => $item = str_pad($item, 2, '0', STR_PAD_LEFT));
  array_walk($solution_range, fn(&$item) => $item = str_pad($item, 2, '0', STR_PAD_LEFT));

  // Store in the database as a plain string.
  $challenge = implode(',', $challenge_range);
  $solution = implode(',', $solution_range);

  $request = $smcFunc['db_query']('', '
    insert into {db_prefix}gw_captcha
    (challenge, solution, ip, ip2)
    values
    ({string:challenge}, {string:solution}, {string:ip}, {string:ip2})
  ',
    [
      'challenge' => $challenge,
      'solution' => $solution,
      'ip' => $user_info['ip'],
      'ip2' => $user_info['ip2'],
    ]
  );
  $id = $smcFunc['db_insert_id']('{db_prefix}gw_captcha', 'id_challenge');

  return [
    'id' => $id,
    'challenge' => $challenge_range,
    'solution' => $solution_range,
  ];
}

/**
 * Verifies a captcha challenge.
 * 
 * Returns true or false depending on whether the solution was correct.
 * If the solution is incorrect, a reason is returned as well.
 */
function verify_captcha_challenge($challenge_id, $user_solution_idx) {
  $captcha = _get_captcha_by_id($challenge_id);

  // Ensure we're looking at a valid captcha ID.
  if (empty($captcha)) {
    return [false, 'not_found'];
  }

  // Mark an attempt on this captcha.
  increment_captcha_attempts($challenge_id);

  // Don't let a user solve an old captcha.
  if ($captcha['status'] !== 'unsolved') {
    return [false, 'already_solved'];
  }

  // All these arrays will be zero padded.
  $user_solution_idx = explode(',', $user_solution_idx);
  $challenge = explode(',', $captcha['challenge']);
  $solution = explode(',', $captcha['solution']);

  // The user's solution contains the indices for the correct solutions.
  $user_solution = [];
  foreach ($user_solution_idx as $idx) {
    $user_solution[] = $challenge[intval($idx)];
  }
  $user_solution = implode(',', $user_solution);

  if ($user_solution !== $captcha['solution']) {
    return [false, 'incorrect'];
  }

  // Mark the captcha as solved.
  mark_captcha_as_solved($challenge_id);

  return [true, ''];
}

/**
 * Deletes all unsolved challenges older than 2 hours.
 */
function remove_old_captcha_challenges() {
  global $db_prefix, $smcFunc;
  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}gw_captcha
    where status = {string:status}
    and created_at < {raw:age}
  ',
    [
      'status' => 'unsolved',
      'age' => 'now() - interval 2 hour'
    ]
  );
}

/**
 * Increments a captcha's attempts count by 1.
 */
function increment_captcha_attempts($challenge_id) {
  global $db_prefix, $smcFunc;
  $request = $smcFunc['db_query']('', '
    update {db_prefix}gw_captcha
    set attempts = attempts + 1
    where id_challenge = {int:id}
  ',
    [
      'id' => $challenge_id
    ]
  );
}

/**
 * Marks a captcha as having been solved.
 */
function mark_captcha_as_solved($challenge_id) {
  global $db_prefix, $smcFunc;
  $request = $smcFunc['db_query']('', '
    update {db_prefix}gw_captcha
    set solved_at = {raw:solved_at}, status = {string:status}
    where id_challenge = {int:id}
  ',
    [
      'id' => $challenge_id,
      'status' => 'solved',
      'solved_at' => 'now()',
    ]
  );
}

/**
 * Returns data for the images we'll use in the captcha.
 * 
 * This includes all images as base64-encoded strings.
 * Normally the data is loaded from a JSON cache file that lives for one month;
 * otherwise, we glob the files from disk.
 */
function get_captcha_image_data() {
  global $captcha_cache_file;

  // Check if we have a cache file for the image data.
  if (file_exists($captcha_cache_file) && time() - filemtime($captcha_cache_file) < (30 * 24 * 60 * 60)) {
    return json_decode(file_get_contents($captcha_cache_file), true);
  }
  // Regenerate the data from disk.
  $data = _get_captcha_image_data_from_disk();
  file_put_contents($captcha_cache_file, json_encode($data));
  return $data;
}

/**
 * Returns all available captcha images.
 */
function _get_captcha_image_data_from_disk() {
  global $captcha_basedir;

  $type1 = _get_captcha_image_data($captcha_basedir.'/foget/');
  $type2 = _get_captcha_image_data($captcha_basedir.'/regular/');

  return [
    'type1' => $type1,
    'type2' => $type2,
    'time' => time(),
  ];
}

/**
 * Globs all .gif files in a directory.
 */
function _get_captcha_image_data($dir) {
  $dir = rtrim($dir, '/').'/';
  $files = glob($dir.'*.gif');
  $images = [];
  foreach ($files as $file) {
    $filepath = realpath($file);
    $binary = file_get_contents($filepath);
    $base64 = base64_encode($binary);
    $path = pathinfo($filepath);
    $mime = guess_mime_type($filepath);
    $images['img_'.$path['filename']] = [
      'name' => $path['filename'],
      'filetype' => $path['extension'],
      'prefix' => 'data:'.$mime[0].';base64,',
      'data' => $base64,
    ];
  }
  return $images;
}

/**
 * Returns whether a captcha table has been created.
 */
function _has_captcha_table() {
  return table_exists('gw_captcha');
}

/**
 * Adds the captcha table to the database.
 */
function _create_captcha_table() {
  global $db_prefix, $smcFunc;

  $request = $smcFunc['db_query']('', '
    create table {db_prefix}gw_captcha (
      id_challenge bigint unsigned not null auto_increment,
      challenge varchar(255) not null default \'\',
      solution varchar(255) not null default \'\',
      created_at timestamp default current_timestamp,
      solved_at timestamp null default null,
      status enum(\'unsolved\', \'solved\') not null default \'unsolved\',
      attempts int unsigned default 0,
      ip varchar(255) null default null,
      ip2 varchar(255) null default null,
      primary key (id_challenge),
      key idx_created_at (created_at)
    ) engine=InnoDB
  ');

  return !!$request;
}

/**
 * Returns a captcha by ID.
 */
function _get_captcha_by_id($id) {
  global $db_prefix, $smcFunc;
  
  $request = $smcFunc['db_query']('', '
    select * from {db_prefix}gw_captcha
    where id_challenge = {int:id}
    limit 1
  ',
    [
      'id' => $id
    ]
  );

  $result = $smcFunc['db_fetch_assoc']($request);

  return $result;
}

/**
 * Returns a row of image tags for the challenge.
 */
function _generate_emoticon_tags($range, $type, $links, $images) {
  $tags = [];
  for ($n = 0; $n < count($range); ++$n) {
    $idx = $range[$n];
    $image = $images[$type]['img_'.$idx];
    $tags[] = implode('', [
      $links ? '<a href="#">' : '',
      '<img src="'.$image['prefix'].$image['data'].'" id="n_'.$n.'" />',
      $links ? '</a>' : '',
    ]);
  }
  return $tags;
}
