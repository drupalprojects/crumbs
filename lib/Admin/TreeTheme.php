<?php


class crumbs_Admin_TreeTheme {

  protected $basename;

  function __construct($basename) {
    $this->basename = $basename;
  }

  function wrap($html) {
    return <<<EOT
<div class="crumbs-weights-widget">
  <div class="row rules-head clearfix">
    <div class="cell rule-weight">Weight</div>
    <div class="cell rule-key">Key</div>
    <div class="cell rule-title">Title</div>
  </div>
  <ul>$html</ul>
</div>
EOT;
  }

  function tree($key, $options, $children) {
    $class = $this->getRowClass($key, $options);
    $weight_cell = $this->weightCell($key, @$options['explicit_weight']);
    $title = @$options['title'];
    $html = <<<EOT
<div class="row rule-info clearfix">
  <div class="cell rule-weight">$weight_cell</div>
  <div class="cell rule-key">$key</div>
  <div class="cell rule-title">$title</div>
</div>
EOT;
    if (!empty($children)) {
      $html .= '<ul class="rule-children">' . implode('', $children) . '</ul>';
    }
    return <<<EOT
<li class="$class">$html</li>
EOT;
  }

  protected function getRowClass($key, $options) {
    $class = 'rule';
    if (isset($options['default_weight'])) {
      $default_weight = $this->weightToString($options['default_weight']);
      $class .= ' rule-default_weight-' . $default_weight;
    }
    return $class;
  }

  protected function weightCell($key, $weight) {
    $name = check_plain($this->basename . '[' . $key . ']');
    $weight = $this->weightToString($weight);
    $html = <<<EOT
<input name="$name" value="$weight" />
EOT;
    return $html;
  }

  protected function weightToString($weight) {
    $w =
      !isset($weight)     ? '' : (
      ($weight === 0)     ? '' : (
      ($weight === '0')   ? '' : (
      ($weight < 0)       ? 'false' : (
      ($weight === FALSE) ? 'false' : (
      (int)$weight
    )))));
    return $w;
  }
}

