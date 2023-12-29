<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// Template for editing a ban (i.e. adding a member to an existing ban group).
// <http://vesuvius.local/gw/index.php?action=admin;area=ban;sa=add;u=50842>
function template_ban_edit() {
  return render_template('pages/moderation/ban_edit.twig', [
    'ban_groups_meta' => get_ban_groups_metadata(),
    'ban_suggestion' => get_ban_suggestion_member_info(),
  ]);
}
