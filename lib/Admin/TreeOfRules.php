<?php


class crumbs_Admin_TreeOfRules {

  protected $hierarchy = array();
  protected $options = array();
  protected $theme;

  function __construct($available_keys, $weights, $disabled_keys, $theme) {
    $this->buildHierarchy($available_keys);
    foreach ($disabled_keys as $key) {
      $this->options[$key]['default_weight'] = FALSE;
    }
    foreach ($weights as $key => $weight) {
      $this->options[$key]['explicit_weight'] = $weight;
    }
    $this->theme = $theme;
  }

  protected function buildHierarchy($rules) {
    foreach ($rules as $key => $title) {
      $options[$key]['title'] = $title;
      if (FALSE !== $parent_key = $this->keyGetParent($key)) {
        $this->hierarchy[$parent_key][$key] = TRUE;
      }
    }
  }

  protected function keyGetParent($key) {

    if ('*' === $key) {
      return FALSE;
    }

    if ('.*' === substr($key, -2)) {
      $key = substr($key, 0, -2);
    }

    if (FALSE !== $pos = strpos($key, '.')) {
      return substr($key, 0, $pos) . '.*';
    }
    else {
      return '*';
    }
  }

  function render($start = '*') {
    $html = $this->renderTree('*');
    return $this->theme->wrap($html);
  }

  function renderTree($parent_key) {
    $children = array();
    if (!empty($this->hierarchy[$parent_key])) {
      foreach ($this->hierarchy[$parent_key] as $key => $true) {
        $children[$key] = $this->renderTree($key);
      }
    }
    $options = isset($this->options[$parent_key]) ? $this->options[$parent_key] : array();
    return $this->theme->tree($parent_key, $options, $children);
  }
}
