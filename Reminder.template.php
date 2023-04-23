<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The password reminder page. This mails you a password reset email.
// <http://vesuvius.local/gw/index.php?action=reminder>
function template_main() {
  return render_template('pages/authentication/form_pwreset.twig');
}

// The picker that lets you choose either an email or a secret question.
// <http://vesuvius.local/gw/index.php?action=reminder;sa=picktype>
function template_reminder_pick() {
  return render_template('pages/authentication/form_pwreset_pick.twig');
}

// After choosing to get an email with a password reset link.
// <http://vesuvius.local/gw/index.php?action=reminder;sa=picktype>
function template_sent() {
  return render_template('pages/authentication/form_pwreset_sent.twig');
}

// Allows a new password to be set. This link is sent to the user by email.
// <http://vesuvius.local/gw/index.php?action=reminder;sa=setpassword;u=1;code=f473cf84c2>
function template_set_password() {
  return render_template('pages/authentication/form_pwreset_set.twig');
}

// Asks the secret question and optionally allows a new password to be set.
// <http://vesuvius.local/gw/index.php?action=secret;sa=setpassword;u=1;code=f473cf84c2>
function template_ask() {
  return render_template('pages/authentication/form_pwreset_ask.twig');
}
