<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $gw_custom_fields;

$gw_custom_fields = [
  [
    'type' => 'social_media',
    'fields' => [
      [
        'field' => 'cust_discor',
        'name' => 'Discord',
      ],
      [
        'field' => 'cust_discor0',
        'name' => 'Discord server',
        'is_link' => true,
      ],
      [
        'field' => 'cust_twitte',
        'name' => 'Twitter',
        'is_link' => true,
      ],
      [
        'field' => 'cust_instag',
        'name' => 'Instagram',
        'is_link' => true,
      ],
      [
        'field' => 'cust_mastod',
        'name' => 'Mastodon',
        'is_link' => true,
      ],
      [
        'field' => 'cust_facebo',
        'name' => 'Facebook',
        'is_link' => true,
      ],
      [
        'field' => 'cust_twitch',
        'name' => 'Twitch',
        'is_link' => true,
      ],
      [
        'field' => 'cust_youtub',
        'name' => 'Youtube',
        'is_link' => true,
      ],
      [
        'field' => 'cust_tiktok',
        'name' => 'TikTok',
        'is_link' => true,
      ],
      [
        'field' => 'cust_bandca',
        'name' => 'Bandcamp',
        'is_link' => true,
      ],
      [
        'field' => 'cust_soundc',
        'name' => 'SoundCloud',
        'is_link' => true,
      ],
      [
        'field' => 'cust_itch',
        'name' => 'Itch.io profile',
        'is_link' => true,
      ],
      [
        'field' => 'cust_steam',
        'name' => 'Steam profile',
        'is_link' => true,
      ],
      [
        'field' => 'cust_xboxli',
        'name' => 'Xbox Live username',
      ],
      [
        'field' => 'cust_ninten',
        'name' => 'Nintendo Online username',
      ],
      [
        'field' => 'cust_playst',
        'name' => 'PSN username',
      ],
      [
        'field' => 'cust_irc',
        'name' => 'IRC',
      ],
    ]
  ]
];

/**
 * Returns all custom profile fields including metadata.
 * 
 * This will return different fields depending on what page is being viewed.
 */
function get_expanded_custom_fields() {
  global $context;
  $gw_metadata = get_gw_metadata();
  $custom_fields = get_custom_fields($gw_metadata);
  return $custom_fields;
}

/**
 * Returns all custom fields in the context variable.
 * 
 * All custom fields are merged with GW metadata and converted to profile fields.
 */
function get_custom_fields($gw_metadata = []) {
  global $context;
  $fields = [];
  for ($n = 0; $n < count($context['custom_fields']); ++$n) {
    $field_data = $context['custom_fields'][$n];
    $slug = $field_data['colname'];
    $metadata = $gw_metadata[$slug] ?? [];
    $fields[] = custom_to_profile($field_data, $slug, $n, $metadata);
  }
  return array_column($fields, null, '_slug');
}

/**
 * Converts a custom field item to be more like a profile field.
 * 
 * This is used to facilitate displaying them on the profile form field later.
 */
function custom_to_profile($field_data, $slug, $n, $metadata = []) {
  $field_data = array_merge($field_data, $metadata);
  return array_merge($field_data, [
    'label' => $field_data['name'],
    'subtext' => $field_data['desc'],
    '_slug' => $slug,
    '_custom_field_order' => $n,
    '_is_custom_field' => true
  ]);
}

/**
 * Returns the Gaming World custom fields metadata.
 * 
 * This returns the content of $gw_custom_fields, modified for easier consumption.
 */
function get_gw_metadata() {
  global $gw_custom_fields;
  $fields = [];
  foreach ($gw_custom_fields as $group) {
    for ($n = 0; $n < count($group['fields']); ++$n) {
      $field_data = $group['fields'][$n];
      $fields[$field_data['field']] = array_merge($field_data, [
        '_is_gw_custom_field' => true,
        '_custom_field_type' => $group['type'],
        '_custom_field_order' => $n,
      ]);
    }
  }
  return $fields;
}
