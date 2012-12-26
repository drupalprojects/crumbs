<?php


function crumbs_admin_form() {
  $form = array();

  // drupal_add_js(drupal_get_path('module', 'crumbs') .'/crumbs.admin.js');
  // drupal_add_css(drupal_get_path('module', 'crumbs') .'/crumbs.admin.css');

  $form['crumbs_show_current_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the current page at the end of the breadcrumb trail.'),
    '#default_value' => variable_get('crumbs_show_current_page', FALSE),
  );

  $form['crumbs_weights'] = array(
    '#type' => 'crumbs_weights_widget',
    '#title' => t('Order of criteria for parent-finding.'),
    '#default_value' => crumbs_get_weights(),
  );

  return system_settings_form($form);
}

/**
 * Default theme implementation for crumbs_weights_widget.
 */
function theme_crumbs_weights_widget($vars) {

  $element = $vars['element'];

  list($plugins, $disabled_keys) = crumbs_get_plugins();
  list($available_keys, $keys_by_plugin) = _crumbs_load_available_keys($plugins);
  $weights = $element['#default_value'];

  $theme = new crumbs_Admin_TreeTheme($element['#name']);
  $tree = new crumbs_Admin_TreeOfRules($available_keys, $weights, $disabled_keys, $theme);

  return $tree->render($theme);
}


function _crumbs_load_available_keys(array $plugins) {
  // we can't use the plugin engine,
  // or else we would miss disabled plugins.
  $plugin_operation = new crumbs_PluginOperation_describe();
  foreach ($plugins as $plugin_key => $plugin) {
    $plugin_operation->invoke($plugin, $plugin_key);
  }
  return array($plugin_operation->getKeys(), $plugin_operation->getKeysByPlugin());
}
