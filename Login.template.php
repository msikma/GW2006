<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The user login page.
// <http://vesuvius.local/gw/index.php?action=login>
function template_login() {
  return render_template('pages/authentication/form_login_user.twig');
}

// The admin login page.
function template_admin_login() {
  return render_template('pages/authentication/form_login_admin.twig');
}

// Warning message for guests trying to access an area that's only accessible to registered users.
// E.g. <http://vesuvius.local/gw/index.php?action=admin> as guest
function template_kick_guest() {
  return render_template('pages/authentication/form_login_user.twig', ['_guest_warning' => true]);
}

// Maintenance mode page. Contains the login page.
function template_maintenance() {
  return render_template('pages/authentication/form_login_user.twig', ['_maintenance_mode' => true]);
}

// User activation form.
// <http://vesuvius.local/gw/index.php?action=activate&u=123>
function template_retry_activate() {
  return render_template('pages/authentication/form_activation.twig');
}

// Form to request for an account activation code to be resent.
// <http://vesuvius.local/gw/index.php?action=activate>
function template_resend() {
  return render_template('pages/authentication/form_activation_resend.twig');
}
