<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('lib/context.php');
require_once('twig.php');

// Form that lets a user send a topic to a friend by email.
// <http://vesuvius.local/gw/index.php?action=emailuser;sa=sendtopic;topic=1234.0>
function template_main() {
  return render_template('pages/etc/send_to_friend.twig', ['is_deprecated' => true]);
}

// Form for sending emails to forum users.
// <http://vesuvius.local/gw/index.php?action=emailuser;sa=email;msg=12345>
function template_custom_email() {
  return render_template('pages/etc/email_member.twig', ['is_deprecated' => true]);
}

// Allows a user to report a post to the moderators.
// <http://vesuvius.local/gw/index.php?action=reporttm;topic=1234.0;msg=123456>
function template_report() {
  return render_template('pages/moderation/report_topic.twig', [
    'visual_verification' => get_visual_verification(),
  ]);
}
