<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

set_include_path(get_include_path() . PATH_SEPARATOR . $settings['theme_dir']);

require_once('twig.php');

// Displayed on PM pages before the main content.
function template_pm_above() {
  return render_template('pages/pm/above.twig');
}

// Displayed on PM pages after the main content.
function template_pm_below() {
  // Nothing here.
}

// The overview of all PMs, and the starting page.
// <http://vesuvius.local/gw/index.php?action=pm>
// <http://vesuvius.local/gw/index.php?action=pm;sa=folder>
function template_folder() {
  return render_template('pages/pm/pm_folder.twig', collect_pms());
}

// Template for searching through private messages.
// <http://vesuvius.local/gw/index.php?action=pm;sa=search>
function template_search() {
  return render_template('pages/pm/search_form.twig');
}

// Search results for the user's query.
// <http://vesuvius.local/gw/index.php?action=pm;sa=search2>
function template_search_results() {
  return render_template('pages/pm/search_results.twig', ['page_index_context' => get_page_index_context($modSettings['defaultMaxMessages'])]);
}

// Send a new private message.
// <http://vesuvius.local/gw/index.php?action=pm;sa=send>
// <http://vesuvius.local/gw/index.php?action=pm;sa=send2>
function template_send() {
  return render_template('pages/pm/form_new_message.twig');
}

// This template asks the user whether they wish to empty out a folder.
// <http://vesuvius.local/gw/index.php?action=pm;sa=removeall>
function template_ask_delete() {
  return render_template('pages/pm/form_delete_all.twig');
}

// This template asks the user what messages they want to prune.
// <http://vesuvius.local/gw/index.php?action=pm;sa=prune>
function template_prune() {
  return render_template('pages/pm/form_prune.twig');
}

// Page for editing and adding new labels.
// <http://vesuvius.local/gw/index.php?action=pm;sa=manlabels>
function template_labels() {
  return render_template('pages/pm/form_labels.twig');
}

// Template for reporting a private message.
// http://vesuvius.local/gw/index.php?action=pm;sa=report;l=-1;pmsg=1234
function template_report_message() {
  return render_template('pages/pm/form_report.twig');
}

// Confirmation page after reporting a private message.
// <http://vesuvius.local/gw/index.php?action=pm;sa=report;l=-1>
function template_report_message_complete() {
  return render_template('pages/pm/form_report_complete.twig');
}

// Page for managing rules that categorize PMs into specific labels.
// <http://vesuvius.local/gw/index.php?action=pm;sa=manrules>
function template_rules() {
  return render_template('pages/pm/form_rules.twig');
}

// Template for editing and adding a new rule.
// <http://vesuvius.local/gw/index.php?action=pm;sa=manrules;add;rid=0>
function template_add_rule() {
  return render_template('pages/pm/form_rules_add.twig');
}
