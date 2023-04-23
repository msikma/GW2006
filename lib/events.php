<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

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
