<?php


class crumbs_ServiceFactory {

  function breadcrumbBuilder($cache) {
    return new crumbs_BreadcrumbBuilder($cache->pluginEngine);
  }

  function trailFinder($cache) {
    return new crumbs_TrailFinder($cache->parentFinder);
  }

  function parentFinder($cache) {
    return new crumbs_ParentFinder($cache->pluginEngine);
  }

  function pluginEngine($cache) {
    list($plugins, $disabled_keys) = crumbs_get_plugins();
    $weights = crumbs_get_weights();
    foreach ($disabled_keys as $key => $disabled) {
      if (!isset($weights[$key])) {
        $weights[$key] = FALSE;
      }
    }
    return new crumbs_PluginEngine($plugins, $weights);
  }
}
