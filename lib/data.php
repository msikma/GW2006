<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/db.php');

/**
 * Returns a link to a member's profile.
 */
function get_member_profile_link($id, $name, $modified_name) {
  global $scripturl, $txt;
  $url = "{$scripturl}?action=profile;u={$id}";
  $username = !empty($modified_name) ? $modified_name : $name;
  $title = implode(' ', [$txt['profile_of'], $username]);
  return '<a href="'.$url.'" title="'.$title.'">'.$username.'</a>';
}

/**
 * Returns a URL to the last post in a topic.
 */
function get_last_post_url($topic_id, $last_post_id) {
  global $scripturl;
  return "{$scripturl}?topic={$topic_id}.msg{$last_post_id}#new";
}

/**
 * Returns the timestamp for a post.
 */
function get_post_timestamp($post_time) {
  return timeformat($post_time);
}

/**
 * Extracts the redirect targets for when a post is a redirect.
 * 
 * Redirect topics have the target marked in a [iurl] tag.
 */
function get_post_redirect_target($post) {
  $body = $post['body'];
  preg_match_all('/\[iurl\](.+?)\[\/iurl\]/m', $body, $matches);
  $url = $matches[1][0];
  preg_match('/topic=([0-9]+)\./', $url, $matches);
  $id = intval($matches[1]);
  $post_data = [];
  if (!empty($id)) {
    $post_data = get_topic_data($id);
  }
  return array_merge([
    'topic_link' => $url,
  ], $post_data);
}
