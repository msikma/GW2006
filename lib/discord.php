<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

require_once('lib/context.php');
require_once('lib/cache.php');

/**
 * Returns Discord server presence info (number of members online, etc.) from the public widget API.
 * 
 * The data is cached for 5 minutes.
 */
function get_discord_presence_info($guild_id, $refresh = false) {
  $key_base = 'gw2006_discord_info';
  $key = $key_base.':'.$guild_id;

  if (!$refresh) {
    $cache = get_cached_data($key);
  }

  // If our data is outdated, get up-to-date information and store it.
  if ($refresh || $cache['not_found'] || $cache['is_stale']) {
    $data = _fetch_live_discord_presence_info($guild_id);
    set_cached_data($key, $data, 300);
    return $data;
  }

  return $cache['data'];
}

/**
 * Fetches the live Discord server presence info for a given guild ID.
 */
function _fetch_live_discord_presence_info($guild_id) {
  $data = json_decode(file_get_contents('https://discord.com/api/guilds/'.$guild_id.'/widget.json'), true);
  return $data;
}
