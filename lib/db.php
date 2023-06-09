<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/data.php');

/**
 * SMF provides us with a list of users whose birthday it is today.
 * 
 * However, this list does not include their member groups.
 * We need those to display the correct member color in the list.
 * So this function looks up the member groups and returns an updated array.
 */
function add_birthday_member_groups($birthdays_list) {
  global $db_prefix, $smcFunc;

  if (empty($birthdays_list)) {
    return $birthdays_list;
  }

  // Index the birthdays by ID so we can easily inject the new data.
  $birthdays = array_column($birthdays_list, null, 'id');
  $birthday_ids = array_keys($birthdays);
  
  // Retrieve all member groups for members whose birthday is today or upcoming.
  $request = $smcFunc['db_query']('', '
    select m.id_member, m.id_group, mg.group_name from {db_prefix}members as m
    left join {db_prefix}membergroups as mg on m.id_group = mg.id_group
    where m.id_member in ({array_int:members})
  ',
    [
      'members' => $birthday_ids
    ]
  );

  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $birthdays[$row['id_member']]['_group'] = $row['group_name'];
    $birthdays[$row['id_member']]['_group_id'] = $row['id_group'];
  }
  
  // Convert the birthdays back to a plain list.
  return array_values($birthdays);
}

/**
 * Retrieves basic data about a post by its ID.
 */
function get_post_data($post_id) {
  global $db_prefix, $smcFunc;

  if (empty($post_id)) {
    return null;
  }

  // Get the topic data.
  $request = $smcFunc['db_query']('', '
    select
      p.id_msg, p.id_topic, p.id_board, p.poster_time, p.id_member, p.id_msg_modified,
      p.subject, p.poster_name, p.poster_email, p.modified_time, p.modified_name, p.body, p.approved
    from {db_prefix}messages as p
    where p.id_msg = {int:post_id}
  ',
    [
      'post_id' => $post_id
    ]
  );

  if ($smcFunc['db_num_rows']($request) === 0) {
    return null;
  }
  
  $data = $smcFunc['db_fetch_assoc']($request);

  return array_merge([
    '_member_link' => get_member_profile_link($data['id_member'], $data['poster_name'], $data['modified_name']),
    '_post_time' => get_post_timestamp($data['poster_time']),
  ], $data);
}

/**
 * Retrieves basic data about a topic by its ID.
 */
function get_topic_data($topic_id) {
  global $db_prefix, $smcFunc;

  if (empty($topic_id)) {
    return null;
  }

  // Get the topic data.
  $request = $smcFunc['db_query']('', '
    select
      t.id_topic, t.is_sticky, t.id_board, t.id_first_msg, t.id_last_msg, t.id_member_started, t.id_member_updated,
      t.num_replies, t.num_views, t.locked, t.unapproved_posts, t.approved
    from {db_prefix}topics as t
    where t.id_topic = {int:topic_id}
  ',
    [
      'topic_id' => $topic_id
    ]
  );

  if ($smcFunc['db_num_rows']($request) === 0) {
    return null;
  }
  
  $data = $smcFunc['db_fetch_assoc']($request);
  if (empty($data)) {
    return null;
  }

  $first_post = get_post_data($data['id_first_msg']);
  $last_post = get_post_data($data['id_last_msg']);
  
  return [
    'topic' => array_merge($data, ['_last_post_url' => get_last_post_url($data['id_topic'], $last_post['id_msg'])]),
    'first_post' => $first_post,
    'last_post' => $last_post,
  ];
}
