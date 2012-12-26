<?php


class crumbs_Admin_TreeOfRules {

  protected $theme;

  function __construct($hierarchy, $options, $theme) {
    
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
    $options = $this->getTreeOptions($parent_key);
    return $this->theme->tree($parent_key, $options, $children);
  }

  function getTreeOptions($key) {
    return array(
    );
  }
}
