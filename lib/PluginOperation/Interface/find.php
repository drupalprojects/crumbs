<?php

/**
 * Interface to be used internally by the plugin engine.
 */
interface crumbs_PluginOperation_Interface_find {
  function invoke($plugin, $plugin_key, $weight_keeper);
}
