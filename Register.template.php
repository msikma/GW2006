<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The registration agreement shown to users before they see the registration form.
// <http://vesuvius.local/gw/index.php?action=register>
function template_registration_agreement() {
  return render_template('pages/authentication/form_register_agreement.twig');
}

// Form for getting the user's information before registering them.
// <http://vesuvius.local/gw/index.php?action=register>
function template_registration_form() {
  return render_template('pages/authentication/form_register.twig', [
		'profile_fields' => get_expanded_profile_fields(false),
		'visual_verification' => get_visual_verification()
	]);
}

// Displayed after registration.
// <http://vesuvius.local/gw/index.php?action=register>
function template_after() {
  return render_template('pages/authentication/form_register_done.twig');
}

// Instructions for COPPA registration.
// <http://vesuvius.local/gw/index.php?action=coppa;member=50792>
function template_coppa() {
  return render_template('pages/article/coppa_instructions.twig');
}

// Printable form for a parent or guardian to grant permission for a minor to access the forum.
// <http://vesuvius.local/gw/index.php?action=coppa;form;member=50792>
function template_coppa_form() {
  return render_template('pages/print/coppa_form.twig');
}

// Pop-up window showing the registration verification code as audio file.
// <http://vesuvius.local/gw/index.php?action=verificationcode;vid=register;rand=a06cd3da2a2a8aef7f53798f816762f7;sound>
function template_verification_sound() {
  return render_template('pages/popup/register_audio.twig');
}

// Admin center page for registering a new user.
// <http://vesuvius.local/gw/index.php?action=admin;area=regcenter;sa=register;e6570c04=81ade41cf178d02a84715e60b49d8174>
function template_admin_register() {
	return render_template('pages/admin/form_register_new_user.twig');
}

// Form for editing the agreement shown while registering.
// <http://vesuvius.local/gw/index.php?action=admin;area=regcenter;sa=agreement;e6570c04=81ade41cf178d02a84715e60b49d8174>
function template_edit_agreement() {
	return render_template('pages/admin/form_edit_agreement.twig');
}

// Form for editing reserved words that are prohibited to use in usernames.
// <http://vesuvius.local/gw/index.php?action=admin;area=regcenter;sa=reservednames;e6570c04=81ade41cf178d02a84715e60b49d8174>
function template_edit_reserved_words() {
	return render_template('pages/admin/form_reserved_words.twig');
}

// Form for editing the privacy policy shown on the registration page.
// <http://vesuvius.local/gw/index.php?action=admin;area=regcenter;sa=policy;e6570c04=81ade41cf178d02a84715e60b49d8174>
function template_edit_privacy_policy() {
	return render_template('pages/admin/form_register_privacy_policy.twig');
}
