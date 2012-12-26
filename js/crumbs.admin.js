jQuery(function($){

  function scanWidget(widget) {
    var scanned = {};
    $('.rule-weight > input', widget).each(function(){
      var info = {};
      info.$input = $(this);
      info.name = info.$input.attr('name');
      info.key = info.name.split('[').pop().split(']')[0];
      info.$li = info.$input.parent().parent().parent();
      info.$bar = $('<div class="bar">').append('<div class="spot">\' &nbsp;</div>').prependTo(info.$li);
      info.$input.parent().append('&nbsp;');
      info.$input.hide();
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
      max = Math.max(assoc[k], max);
    }
    return max;
  }

  function setWeights(widget, scanned, weights) {
    var max_weight = maxAssoc(weights);
    console.log(widget, scanned, weights, max_weight);
    $('.rule-weight', widget).css('width', (20 * max_weight + 80) + 'px');
    for (var k in scanned) {
      var info = scanned[k];
      var weight = weights[k];

      switch (weight) {

        case '':
          info.$bar.css('margin-left', (20 * max_weight + 30) + 'px');
          info.$bar.css('padding-right', 0);
          info.$li.addClass('inherit');
          break;

        case '-1':
        case 'false':
          info.$bar.css('margin-left', (20 * max_weight + 60) + 'px');
          info.$bar.css('padding-right', 0);
          info.$li.addClass('disabled');
          break;

        default:
          info.$bar.css('margin-left', (20 * (max_weight - weight) + 9) + 'px');
          info.$bar.css('padding-right', (weight * 20 + 9) + 'px');
          info.$li.addClass('enabled');
      }
    }
  }

  $('.crumbs-weights-widget').each(function(){
    var scanned = scanWidget(this);
    var weights = readWeights(scanned);
    setWeights(this, scanned, weights);
  });
});

