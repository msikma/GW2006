<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// The calendar view. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=calendar>
// <http://vesuvius.local/gw/index.php?action=calendar;month=1;year=2023>
// <http://vesuvius.local/gw/index.php?action=calendar;viewweek;year=2023;month=2;day=0>
function template_main() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'The calendar functionality is disabled.']);
}

// Template for posting new calendar events. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=calendar;sa=post;month=7;year=2023;a6c1afce5=42d5064c73ccacd0c1dfa55335d8d669>
function template_event_post() {
  return render_template('pages/error_not_implemented.twig', ['message' => 'The calendar functionality is disabled.']);
}

// Displays a monthly calendar grid. FIXME_NOT_IMPLEMENTED
function template_show_month_grid($grid_name) {
  return;
}

// Displays a weekly calendar grid. FIXME_NOT_IMPLEMENTED
function template_show_week_grid($grid_name) {
  return;
}

// A clock. I don't know why this exists, but it does. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=clock>
function template_bcd() {
  return;
}

// Another clock. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=clock;rb>
function template_hms() {
  return;
}

// Yet another clock. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=clock;omfg>
function template_omfg() {
  return;
}

// Displays the time. FIXME_NOT_IMPLEMENTED
// <http://vesuvius.local/gw/index.php?action=clock;thetime>
function template_thetime() {
  return;
}
