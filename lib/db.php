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
 * Returns the member groups that are able to access a given list of forum IDs.
 * 
 * Member groups are returned by ID and slug.
 */
function get_board_member_groups($board_ids = []) {
  global $db_prefix, $smcFunc;

  if (empty($board_ids)) {
    return [];
  }
  
  // Fetch all member groups and permission profiles to get their names.
  $groups = [];
  $request = $smcFunc['db_query']('', 'select id_group, group_name from {db_prefix}membergroups', []);
  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $groups[$row['id_group']] = $row;
  }
  $profiles = [];
  $request = $smcFunc['db_query']('', 'select id_profile, profile_name from {db_prefix}permission_profiles', []);
  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $profiles[$row['id_profile']] = $row;
  }

  // Manually add in the guest and regular member groups, which are special groups with IDs -1 and 0 respectively.
  $groups[-1] = ['id_group' => -1, 'group_name' => 'Guest'];
  $groups[0] = ['id_group' => 0, 'group_name' => 'Regular Member'];

  // Fetch all boards by ID and find which groups are able to access it.
  $boards = [];
  $request = $smcFunc['db_query']('', '
    select id_board, member_groups, id_profile from {db_prefix}boards
    where id_board in ({array_int:board_ids})
  ',
    [
      'board_ids' => $board_ids,
    ]
  );

  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $board_group_ids = explode(',', $row['member_groups']);
    $board_groups = [];
    foreach ($board_group_ids as $board_group_id) {
      $board_groups[$board_group_id] = slug($groups[$board_group_id]['group_name']);
    }
    $boards[$row['id_board']] = [
      'id' => $row['id_board'],
      'profile' => $profiles[$row['id_profile']]['profile_name'],
      'member_groups' => $board_groups,
    ];
  }
  
  return $boards;
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
      p.id_msg, p.id_topic, p.id_board, p.poster_time, p.id_member, p.id_msg_modified, p.icon,
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
 * Retrieves all ban groups from the database.
 */
function get_ban_groups() {
  global $db_prefix, $smcFunc;

  $request = $smcFunc['db_query']('', '
    select * from {db_prefix}ban_groups
    limit 500
  ');

  $ban_groups = [];

  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $ban_groups[$row['id_ban_group']] = $row;
  }

  return $ban_groups;
}

/**
 * Returns all data about a specific member by ID.
 */
function get_member_data($member_id) {
  global $db_prefix, $smcFunc;

  if (empty($member_id)) {
    return null;
  }

  // Get the topic data.
  $request = $smcFunc['db_query']('', '
    select m.*, g.group_name as `group`, g.online_color as group_color, g.id_group as group_id from {db_prefix}members m
    left join {db_prefix}membergroups g
    on m.id_group = g.id_group
    where id_member = {int:member_id}
  ',
    [
      'member_id' => $member_id
    ]
  );

  if ($smcFunc['db_num_rows']($request) === 0) {
    return null;
  }
  
  $data = $smcFunc['db_fetch_assoc']($request);
  if (empty($data)) {
    return null;
  }
  
  return $data;
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

/**
 * Returns the locked status for a list of topic ids.
 * 
 * This is used to determine if topics should be displayed as "regular" locked, or as locked due to old age.
 */
function get_topic_locked_status($topic_ids) {
  global $db_prefix, $smcFunc;

  if (empty($topic_ids)) {
    return [];
  }
  
  $request = $smcFunc['db_query']('', '
    select id_topic, locked from {db_prefix}topics
    where id_topic in ({array_int:topics})
  ',
    [
      'topics' => $topic_ids
    ]
  );

  $topic_locked = [];
  while ($row = $smcFunc['db_fetch_assoc']($request)) {
    $topic_locked[$row['id_topic']] = $row['locked'];
  }
  
  return $topic_locked;
}

/**
 * Returns whether a given table exists.
 */
function table_exists($table_name) {
  global $db_name, $db_prefix, $smcFunc;

  $request = $smcFunc['db_query']('', '
    select count(*) as count
    from information_schema.tables
    where table_schema = {string:db_name}
    and table_name = {string:table_name}
  ',
    [
      'db_name' => $db_name,
      'table_name' => $db_prefix.$table_name
    ]
  );

  $result = $smcFunc['db_fetch_assoc']($request);

  return boolval($result['count']);
}

/**
 * Returns whether a given column exists.
 */
function column_exists($table_name, $column_name) {
  global $db_name, $db_prefix, $smcFunc;

  $request = $smcFunc['db_query']('', '
    select count(*) as count
    from information_schema.columns
    where table_schema = {string:db_name}
    and table_name = {string:table_name}
    and column_name = {string:column_name}
  ',
    [
      'db_name' => $db_name,
      'table_name' => $db_prefix.$table_name,
      'column_name' => $column_name
    ]
  );

  $result = $smcFunc['db_fetch_assoc']($request);

  return boolval($result['count']);
}

/**
 * Returns all deletable posts for a given member.
 * 
 * This will return topics and posts, and will separate these in items we can fully delete,
 * and items we need to "wipe" (removing the post content).
 * 
 * We separate the items into these two categories because sometimes we don't want replies
 * by *other* members to vanish as a result of someone being banned.
 * 
 * This is typically only used when banning spambots, not when banning humans.
 * 
 * TODO: at the moment this does not look for posts by email; only by member id and IP.
 */
function get_banned_member_deletable_posts($ban_suggestions, $allow_search = ['id']) {
  global $db_prefix, $smcFunc, $modSettings;

  // Temporarily disable query checking; normally, subqueries are disabled.
  $oldQueryCheck = $modSettings['disableQueryCheck'];
  $modSettings['disableQueryCheck'] = 1;

  $delete_topics = [];
  $delete_posts = [];
  $wipe_posts = [];

  // Search for posts by id.
  if ($ban_suggestions['id'] && in_array('id', $allow_search)) {
    // Selects all topics that were started by the given member that have either
    // only replies by themselves, or by at most one other person.
    // These are topics we can fully delete without any regard for other replies going missing.
    $request = $smcFunc['db_query']('', '
      select m.id_topic as id from {db_prefix}messages m
      join {db_prefix}topics t on m.id_topic = t.id_topic
      where t.id_member_started = {int:member_id}
      group by m.id_topic
      having sum(m.id_member != {int:member_id}) <= 1
    ',
      [
        'member_id' => $ban_suggestions['id']
      ]
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
      $delete_topics[] = $row['id'];
    }

    // Now get all their dangling messages; these are messages that are the last post in a topic.
    // These are posts that can also be deleted cleanly without any issues.
    $request = $smcFunc['db_query']('', '
      select m.id_msg as id from {db_prefix}messages m
      inner join (
        select id_topic, max(id_msg) as last_msg_id
        from {db_prefix}messages
        group by id_topic
      ) as last_msgs on m.id_msg = last_msgs.last_msg_id
      where m.id_member = {int:member_id}
    ',
      [
        'member_id' => $ban_suggestions['id']
      ]
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
      $delete_posts[] = $row['id'];
    }

    // Finally, get all other messages they posted. These will all be wiped.
    $request = $smcFunc['db_query']('', '
      select m.id_msg as id from {db_prefix}messages m
      where m.id_member = {int:member_id}
      and m.approved != {int:approved_wiped}
      and m.id_msg not in ({array_int:message_ids})
    ',
      [
        'member_id' => $ban_suggestions['id'],
        'approved_wiped' => 2,
        'message_ids' => empty($delete_posts) ? [0] : $delete_posts,
      ]
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
      $wipe_posts[] = $row['id'];
    }
  }

  // Find posts by IP address. See above; this just repeats the same queries, but matching by IP address.
  // Note that these queries are significantly slower.
  if ($ban_suggestions['ip'] && in_array('ip', $allow_search)) {
    $request = $smcFunc['db_query']('', '
      select m.id_topic as id from {db_prefix}messages m
      join {db_prefix}topics t on m.id_topic = t.id_topic
      where t.id_first_msg in (
        select id_msg from {db_prefix}messages
        where poster_ip = {string:member_ip}
      )
      group by m.id_topic
      having sum(m.poster_ip != {string:member_ip}) <= 1
    ',
      [
        'member_ip' => $ban_suggestions['ip']
      ]
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
      $delete_topics[] = $row['id'];
    }

    // TODO: at the moment we don't look for dangling posts by IP.

    $request = $smcFunc['db_query']('', '
      select m.id_msg as id from {db_prefix}messages m
      where m.poster_ip = {string:member_ip}
      and m.id_msg not in ({array_int:message_ids})
    ',
      [
        'member_ip' => $ban_suggestions['ip'],
        'message_ids' => [0], // add dangling posts here later.
      ]
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
      $wipe_posts[] = $row['id'];
    }
  }

  // Re-enable query checking.
  $modSettings['disableQueryCheck'] = $modSettings['disableQueryCheck'];

  return [
    'delete_topics' => array_values(array_unique($delete_topics, SORT_NUMERIC)),
    'delete_posts' => array_values(array_unique($delete_posts, SORT_NUMERIC)),
    'wipe_posts' => array_values(array_unique($wipe_posts, SORT_NUMERIC)),
  ];
}

/**
 * Deletes a number of topics, also deleting any messages posted to the topic.
 * 
 * Used when banning spambots.
 */
function delete_spam_topics($topic_ids) {
  global $db_prefix, $smcFunc;

  if (empty($topic_ids)) {
    return;
  }

  // First, delete all messages from the topic.
  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}messages
    where id_topic in ({array_int:topic_ids})
  ',
    [
      'topic_ids' => $topic_ids,
    ]
  );
  
  // Then delete the topics themselves.
  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}topics
    where id_topic in ({array_int:topic_ids})
  ',
    [
      'topic_ids' => $topic_ids,
    ]
  );
}

/**
 * Deletes a number of messages by id.
 * 
 * Used when banning spambots.
 */
function delete_spam_posts($post_ids) {
  global $db_prefix, $smcFunc;

  if (empty($post_ids)) {
    return;
  }

  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}messages
    where id_msg in ({array_int:post_ids})
  ',
    [
      'post_ids' => $post_ids,
    ]
  );
}

/**
 * Wipes a number of posts by id.
 * 
 * Used when banning spambots.
 * 
 * Wiping a post means removing the post content and setting "approved" to the special value 2.
 */
function wipe_spam_posts($post_ids) {
  global $db_prefix, $smcFunc;

  if (empty($post_ids)) {
    return;
  }

  $request = $smcFunc['db_query']('', '
    update {db_prefix}messages
    set body = {string:new_body}, approved = {int:new_approved}
    where id_msg in ({array_int:post_ids})
  ',
    [
      'new_body' => '[removed]',
      'new_approved' => 2,
      'post_ids' => $post_ids,
    ]
  );
}

/**
 * Wipes a number of members by id.
 * 
 * Used when banning spambots.
 * 
 * Wiping a member means removing all their identifying characteristics.
 */
function wipe_spam_members($member_ids) {
  global $db_prefix, $smcFunc;

  if (empty($member_ids)) {
    return;
  }

  $request = $smcFunc['db_query']('', '
    update {db_prefix}members
    set website_title = {string:website_title},
    website_url = {string:website_url},
    location = {string:location},
    icq = {string:icq},
    aim = {string:aim},
    yim = {string:yim},
    msn = {string:msn},
    signature = {string:signature},
    avatar = {string:avatar},
    usertitle = {string:usertitle}
    where id_member in ({array_int:member_ids})
  ',
    [
      'website_title' => '',
      'website_url' => '',
      'location' => '',
      'icq' => '',
      'aim' => '',
      'yim' => '',
      'msn' => '',
      'signature' => '',
      'avatar' => '',
      'usertitle' => '',
      'member_ids' => $member_ids,
    ]
  );

  $request = $smcFunc['db_query']('', '
    delete from {db_prefix}attachments
    where id_member in ({array_int:member_ids})
  ',
    [
      'member_ids' => $member_ids,
    ]
  );
}
