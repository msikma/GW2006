<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// Any fatal error message.
function template_fatal_error() {
  return render_template('pages/error_fatal.twig');
}

// Error log containing errors generated by members browsing the site.
// <http://vesuvius.local/gw/index.php?action=admin;area=logs;sa=errorlog;desc>
function template_error_log() {
  return render_template('pages/admin/error_log.twig');
}

// Used when viewing the contents of a file that caused an error.
// <http://vesuvius.local/gw/index.php?action=admin;area=logs;sa=errorlog;file=L1VzZXJzL21zaWttYS9Qcm9qZWN0cy9ndzIwMDYvdHdpZy5waHA=;line=102>
function template_show_file() {
  return render_template('pages/popup/file_source.twig');
}
