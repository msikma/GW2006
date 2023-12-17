<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

// Theme member options.
// <http://vesuvius.local/gw/index.php?action=admin;area=theme;th=4;sa=reset>
function template_options() {
  global $context, $settings, $options, $scripturl, $txt;

  $context['theme_options'] = array(
    array(
      'id' => 'show_children',
      'label' => $txt['show_children'],
      'default' => true,
    ),
    array(
      'id' => 'use_sidebar_menu',
      'label' => $txt['use_sidebar_menu'],
      'default' => true,
    ),
    array(
      'id' => 'return_to_post',
      'label' => $txt['return_to_post'],
      'default' => true,
    ),
    array(
      'id' => 'no_new_reply_warning',
      'label' => $txt['no_new_reply_warning'],
      'default' => false,
    ),
    array(
      'id' => 'view_newest_pm_first',
      'label' => $txt['recent_pms_at_top'],
      'default' => true,
    ),
    array(
      'id' => 'posts_apply_ignore_list',
      'label' => $txt['posts_apply_ignore_list'],
      'default' => true,
    ),
    array(
      'id' => 'wysiwyg_default',
      'label' => $txt['wysiwyg_default'],
      'default' => false,
    ),
    array(
      'id' => 'copy_to_outbox',
      'label' => $txt['copy_to_outbox'],
      'default' => true,
    ),
    array(
      'id' => 'pm_remove_inbox_label',
      'label' => $txt['pm_remove_inbox_label'],
      'default' => true,
    ),
    array(
      'id' => 'auto_notify',
      'label' => $txt['auto_notify'],
      'default' => true,
    ),
    array(
      'id' => 'topics_per_page',
      'label' => $txt['topics_per_page'],
      'options' => array(
        0 => $txt['per_page_default'],
        5 => 5,
        10 => 10,
        25 => 25,
        50 => 50,
      ),
      'default' => true,
    ),
    array(
      'id' => 'messages_per_page',
      'label' => $txt['messages_per_page'],
      'options' => array(
        0 => $txt['per_page_default'],
        5 => 5,
        10 => 10,
        25 => 25,
        50 => 50,
      ),
      'default' => true,
    ),
    array(
      'id' => 'calendar_start_day',
      'label' => $txt['calendar_start_day'],
      'options' => array(
        0 => $txt['days'][0],
        1 => $txt['days'][1],
        6 => $txt['days'][6],
      ),
      'default' => true,
    ),
    array(
      'id' => 'display_quick_reply',
      'label' => $txt['display_quick_reply'],
      'options' => array(
        0 => $txt['display_quick_reply1'],
        1 => $txt['display_quick_reply2'],
        2 => $txt['display_quick_reply3']
      ),
      'default' => true,
    ),
    array(
      'id' => 'display_quick_mod',
      'label' => $txt['display_quick_mod'],
      'options' => array(
        0 => $txt['display_quick_mod_none'],
        1 => $txt['display_quick_mod_check'],
        2 => $txt['display_quick_mod_image'],
      ),
      'default' => 1,
    ),
  );
}

// Theme settings.
// <http://vesuvius.local/gw/index.php?action=admin;area=theme;th=4;sa=settings>
function template_settings() {
  global $context, $settings, $options, $scripturl, $txt;

  $context['theme_settings'] = array(
    array(
      'id' => 'show_mark_read',
      'label' => $txt['enable_mark_as_read'],
    ),
    '',
    array(
      'id' => 'show_modify',
      'label' => $txt['last_modification'],
    ),
    array(
      'id' => 'show_profile_buttons',
      'label' => $txt['show_view_profile_button'],
    ),
    array(
      'id' => 'show_user_images',
      'label' => $txt['user_avatars'],
    ),
    array(
      'id' => 'show_blurb',
      'label' => $txt['user_text'],
    ),
  );
}
