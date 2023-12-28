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
        'name' => 'Discord name',
        'icon' => 'socmed_discord.png',
        'show_on_posts' => false,
        'desc' => 'Your Discord username and optionally discriminator (e.g. Dada#1234)',
        'show_description' => true,
      ],
      [
        'field' => 'cust_discor0',
        'name' => 'Discord server',
        'icon' => 'socmed_discord.png',
        'show_on_posts' => true,
        'desc' => 'Invite link to your Discord server',
        'is_link' => true,
        'show_description' => true,
      ],
      [
        'field' => 'cust_twitte',
        'name' => 'Twitter URL',
        'icon' => 'socmed_twitter.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Twitter account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_instag',
        'name' => 'Instagram URL',
        'icon' => 'socmed_instagram.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Instagram account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_mastod',
        'name' => 'Mastodon URL',
        'icon' => 'socmed_mastodon.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Mastodon account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_facebo',
        'name' => 'Facebook URL',
        'icon' => 'socmed_facebook.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Facebook account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_twitch',
        'name' => 'Twitch URL',
        'icon' => 'socmed_twitch.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Twitch account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_youtub',
        'name' => 'Youtube URL',
        'icon' => 'socmed_youtube.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Youtube account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_tiktok',
        'name' => 'TikTok URL',
        'icon' => 'socmed_tiktok.png',
        'show_on_posts' => true,
        'desc' => 'Link to your TikTok account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_bandca',
        'name' => 'Bandcamp URL',
        'icon' => 'socmed_bandcamp.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Bandcamp account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_soundc',
        'name' => 'SoundCloud URL',
        'icon' => 'socmed_soundcloud.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Soundcloud account',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_itch',
        'name' => 'Itch.io URL',
        'icon' => 'socmed_itchio.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Itch.io profile',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_steam',
        'name' => 'Steam profile URL',
        'icon' => 'socmed_steam.png',
        'show_on_posts' => true,
        'desc' => 'Link to your Steam profile',
        'is_link' => true,
        'show_description' => false,
      ],
      [
        'field' => 'cust_irc',
        'name' => 'IRC',
        'icon' => 'socmed_irc.gif',
        'show_on_posts' => true,
        'desc' => 'Link to where you hang out on IRC (e.g. irc://irc.whahay.net/gamingw for channel #gamingw on network irc.whahay.net)',
        'show_description' => false,
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
    'subtext' => $field_data['show_description'] ? $field_data['desc'] : '',
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
