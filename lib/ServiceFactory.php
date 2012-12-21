<?php


class crumbs_ServiceFactory {

  /**
   * A service that can build a breadcrumb from a trail.
   */
  function breadcrumbBuilder($cache) {
    return new crumbs_BreadcrumbBuilder($cache->pluginEngine);
  }

  /**
   * A service that can build a trail for a given path.
   */
  function trailFinder($cache) {
    return new crumbs_TrailFinder($cache->parentFinder);
  }

  /**
   * A service that attempts to find a parent path for a given path.
   */
  function parentFinder($cache) {
    return new crumbs_ParentFinder($cache->pluginEngine);
  }

  /**
   * A service that knows all plugins and their configuration/weights,
   * and can run plugin operations on those plugins.
   */
  function pluginEngine($cache) {
    $plugins = $cache->pluginLibrary->getAvailablePlugins();
    $disabled_keys = $cache->pluginLibrary->getDisabledByDefaultKeys();
    $weights = crumbs_get_weights();
    foreach ($disabled_keys as $key => $disabled) {
      if (!isset($weights[$key])) {
        $weights[$key] = FALSE;
      }
    }
    return new crumbs_PluginEngine($plugins, $weights);
  }

  /**
   * A service that knows about all available plugins and their default
   * settings, but not about their runtime configuration / weights.
   */
  function pluginLibrary($cache) {
    return new crumbs_PluginLibrary();
  }
}
