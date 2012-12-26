jQuery(function($){

  function classesToggle(yes, no) {
    return function($element, v) {
      $element.addClass(v ? yes : no);
      $element.removeClass(v ? no : yes);
    }
  }

  function classesToggleMulti(classes) {
    return function($element, c) {
      for (var i = 0; i < classes.length; ++i) {
        if (c === classes[i] || c === i) {
          $element.addClass(classes[i]);
        }
        else {
          $element.removeClass(classes[i]);
        }
      }
    }
  }

  function setCollapseExpandRecursive($li_parent, expanded) {
    var c = classesToggle('_expanded', '_collapsed');
    if ($li_parent.is('.expanded')) {
      expanded = true;
    }
    else if ($li_parent.is('.collapsed')) {
      expanded = false;
    }
    else {
      return;
    }
    $('> ul > li.inherit', $li_parent).each(function() {
      var $li = $(this);
      c($li, expanded);
      setCollapseExpandRecursive($li, expanded);
    });
  }

  function setCollapseExpand(info, expanded) {
    var c = classesToggle('expanded', 'collapsed');
    c(info.$li, expanded);
    info.$expand.attr('title', expanded ? 'collapse children' : 'expand children');
    info.$expand.html(expanded ? '-' : '+');
    expanded = expanded && !info.$li.is('._collapsed');
    setCollapseExpandRecursive(info.$li, expanded);
  }

  function initCollapseExpand(info, expanded) {
    info.$expand = $('<a>').appendTo(info.$expandCell);
    info.$expand.click(function(){
      expanded = !expanded;
      setCollapseExpand(info, expanded);
    });
    setCollapseExpand(info, expanded);
  }

  function keyGetDepth(key) {
    if (key === '*') {
      return 0;
    }
    if (key.substr(key.length - 2) === '.*') {
      return key.split('.').length - 1;
    }
    else {
      return key.split('.').length;
    }
  }

  function scanElement(input) {
    var info = {};
    info.$input = $(input);
    info.name = info.$input.attr('name');
    info.key = info.name.split('[').pop().split(']')[0];
    info.depth = keyGetDepth(info.key);
    info.$li = info.$input.parent().parent().parent();
    info.$bar = $('<div class="bar">').append('<div class="spot">\' &nbsp;</div>').prependTo(info.$li);
    info.$input.parent().append('&nbsp;');
    info.$input.hide();
    info.$expandCell = $('> div > .rule-toggle-expand', info.$li);
    // info.$expandCell.css('padding-left', (20 * info.depth) + 'px');
    if (info.$li.is('.collapsed')) {
      initCollapseExpand(info, false);
    }
    else if (info.$li.is('.expanded')) {
      initCollapseExpand(info, true);
    }
    else {
      info.$expandCell.html('&nbsp;');
    }
    return info;
  }

  function scanWidget(widget) {
    var scanned = {};
    $('.rule-weight > input', widget).each(function(){
      info = scanElement(this);
      scanned[info.key] = info;
    });
    return scanned;
  }

  function readWeights(scanned) {
    var weights = {};
    for (var k in scanned) {
      weights[k] = scanned[k].$input.val();
    }
    return weights;
  }

  function maxAssoc(assoc) {
    var max = -Infinity;
    for (var k in assoc) {
      if (assoc[k].match(/^\d+$/)) {
       max = Math.max(assoc[k], max);
     }
    }
    return max;
  }

  function setWeights(widget, scanned, weights) {
    var c = classesToggleMulti(['inherit', 'disabled', 'enabled']);
    var max_weight = maxAssoc(weights);
    $('.rule-weight', widget).css('width', (20 * max_weight + 80) + 'px');

    for (var k in scanned) {
      var info = scanned[k];
      var weight = weights[k];

      switch (weight) {

        case '':
          info.$bar.css('margin-left', (20 * max_weight + 30) + 'px');
          info.$bar.css('padding-right', 0);
          c(info.$li, 'inherit');
          break;

        case '-1':
        case 'false':
          info.$bar.css('margin-left', (20 * max_weight + 50) + 'px');
          info.$bar.css('padding-right', 0);
          c(info.$li, 'disabled');
          break;

        default:
          info.$bar.css('margin-left', (20 * (max_weight - weight) + 9) + 'px');
          info.$bar.css('padding-right', (weight * 20 + 9) + 'px');
          c(info.$li, 'enabled');
      }
    }

    for (var k in scanned) {
      var info = scanned[k];
      if (!info.$li.is('.inherit')) {
        setCollapseExpandRecursive(info.$li);
      }
    }
  }

  $('.crumbs-weights-widget').each(function(){
    var scanned = scanWidget(this);console.log(scanned);
    var weights = readWeights(scanned);
    setWeights(this, scanned, weights);
  });
});

