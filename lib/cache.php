<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Stores cached data in the database per the given maximum age.
 */
function set_cached_data($name, $data = [], $max_age = 600) {
  global $db_prefix, $smcFunc;

  $value = [
    'insert_time' => time(),
    'max_age' => intval($max_age),
    'data' => $data,
  ];

  $request = $smcFunc['db_query']('', '
    insert into {db_prefix}settings (variable, value) values ({string:variable}, {string:value})
    on duplicate key update value={string:value}
  ',
    [
      'variable' => $name,
      'value' => json_encode($value),
    ]
  );

  return true;
}

/**
 * Returns a cached value from the database if it's within the maximum age.
 */
function get_cached_data($name, $max_age = 600) {
  global $db_prefix, $smcFunc;
  
  $request = $smcFunc['db_query']('', '
    select variable, value from {db_prefix}settings where variable = {string:variable}
  ',
    [
      'variable' => $name
    ]
  );

  // If the variable wasn't found, return an empty object.
  if ($smcFunc['db_affected_rows']() == 0) {
    return [
      'not_found' => true,
      'is_stale' => null,
      'insert_time' => null,
      'max_age' => null,
      'data' => null,
    ];
  }

  $result = $smcFunc['db_fetch_assoc']($request);
  $value = json_decode($result['value'], true);
  $insert_time = intval($value['insert_time']);
  $max_age = intval($value['max_age']);

  // If the cached data is too old, mark the object as stale.
  $stale = ($insert_time + $max_age) < time();

  return [
    'not_found' => false,
    'is_stale' => $stale,
    'insert_time' => $insert_time,
    'max_age' => $max_age,
    'data' => $value['data'],
  ];
}
