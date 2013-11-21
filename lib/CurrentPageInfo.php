<?php

/**
 * Creates various data related to the current page.
 *
 * The data is provided to the rest of the world via crumbs_Container_LazyData.
 * Each method in here corresponds to one key on the data cache.
 *
 * The $page argument on each method is the data cache itself.
 * The argument can be mocked with a simple stdClass, to test the behavior of
 * each method. (if we had the time to write unit tests)
 */
class crumbs_CurrentPageInfo {

  /**
   * @var crumbs_Container_LazyDataByPath
   */
  protected $trails;

  /**
   * @var crumbs_BreadcrumbBuilder
   */
  protected $breadcrumbBuilder;

  /**
   * @var crumbs_Router
   */
  protected $router;

  /**
   * @param crumbs_Container_LazyDataByPath $trails
   * @param crumbs_BreadcrumbBuilder $breadcrumbBuilder
   * @param crumbs_Router $router
   */
  function __construct($trails, $breadcrumbBuilder, $router) {
    $this->trails = $trails;
    $this->breadcrumbBuilder = $breadcrumbBuilder;
    $this->router = $router;
  }

  /**
   * Check if the breadcrumb is to be suppressed altogether.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return bool
   */
  function breadcrumbSuppressed($page) {
    // @todo Make this work!
    return FALSE;
    $existing_breadcrumb = drupal_get_breadcrumb();
    // If the existing breadcrumb is empty, that means a module has
    // intentionally removed it. Honor that, and stop here.
    return empty($existing_breadcrumb);
  }

  /**
   * Assemble all breadcrumb data.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return array
   */
  function breadcrumbData($page) {
    if (empty($page->breadcrumbItems)) {
      return FALSE;
    }
    return array(
      'trail' => $page->trail,
      'items' => $page->breadcrumbItems,
      'html' => $page->breadcrumbHtml,
    );
  }

  /**
   * Build the Crumbs trail.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return array
   */
  function trail($page) {
    return $this->trails->getForPath($page->path);
  }

  /**
   * Build the raw breadcrumb based on the $page->trail.
   *
   * Each breadcrumb item is a router item taken from the trail, with
   * two additional/updated keys:
   * - title: The title of the breadcrumb item as received from a plugin.
   * - localized_options: An array of options passed to l() if needed.
   *
   * The altering will happen in a separate step, so
   *
   * @param crumbs_Container_LazyPageData $page
   * @return array
   */
  function rawBreadcrumbItems($page) {
    if ($page->breadcrumbSuppressed) {
      return array();
    }
    if (user_access('administer crumbs')) {
      // Remember which pages we are visiting,
      // for the autocomplete on admin/structure/crumbs/debug.
      unset($_SESSION['crumbs.admin.debug.history'][$page->path]);
      $_SESSION['crumbs.admin.debug.history'][$page->path] = TRUE;
      // Never remember more than 15 links.
      while (15 < count($_SESSION['crumbs.admin.debug.history'])) {
        array_shift($_SESSION['crumbs.admin.debug.history']);
      }
    }
    $trail = $page->trail;
    if (count($trail) < $page->minTrailItems) {
      return array();
    }
    if (!$page->showFrontPage) {
      array_shift($trail);
    }
    if (!$page->showCurrentPage) {
      array_pop($trail);
    }
    if (!count($trail)) {
      return array();
    }
    $items = $this->breadcrumbBuilder->buildBreadcrumb($trail);
    if (count($items) < $page->minVisibleItems) {
      // Some items might get lost due to having an empty title.
      return array();
    }
    return $items;
  }

  /**
   * Determine if we want to show the breadcrumb item for the current page.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return bool
   */
  function showCurrentPage($page) {
    return variable_get('crumbs_show_current_page', FALSE) & ~CRUMBS_TRAILING_SEPARATOR;
  }

  /**
   * @param crumbs_Container_LazyPageData $page
   * @return bool
   */
  function trailingSeparator($page) {
    return variable_get('crumbs_show_current_page', FALSE) & CRUMBS_TRAILING_SEPARATOR;
  }

  /**
   * Determine if we want to show the breadcrumb item for the front page.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return bool
   */
  function showFrontPage($page) {
    return variable_get('crumbs_show_front_page', TRUE);
  }

  /**
   * If there are fewer trail items than this, we hide the breadcrumb.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return int
   */
  function minTrailItems($page) {
    return variable_get('crumbs_minimum_trail_items', 2);
  }

  /**
   * Determine separator string, e.g. ' &raquo; ' or ' &gt; '.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return string
   */
  function separator($page) {
    return variable_get('crumbs_separator', ' &raquo; ');
  }

  /**
   * Determine separator string, e.g. ' &raquo; ' or ' &gt; '.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return string
   */
  function separatorSpan($page) {
    return variable_get('crumbs_separator_span', FALSE);
  }

  /**
   * Determine separator string, e.g. ' &raquo; ' or ' &gt; '.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return string
   */
  function richSnippetMode($page) {
    // Only allow rich snippets if theme_breadcrumb() is overridden for the
    // current theme.
    $registry = theme_get_registry(FALSE);
    if (1
      && isset($registry['breadcrumb']['function'])
      && 'crumbs_theme_breadcrumb' === $registry['breadcrumb']['function']
    ) {
      $mode = variable_get('crumbs_rich_snippet_mode', FALSE);
      // Do not return $mode if it is something like '0'.
      if (!empty($mode)) {
        return $mode;
      }
    }
    return FALSE;
  }

  /**
   * If there are fewer visible items than this, we hide the breadcrumb.
   * Every "trail item" does become a "visible item", except when it is hidden:
   * - The frontpage item might be hidden based on a setting.
   * - The current page item might be hidden based on a setting.
   * - Any item where the title is FALSE will be hidden / skipped over.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return int
   */
  function minVisibleItems($page) {
    $n = $page->minTrailItems;
    if (!$page->showCurrentPage) {
      --$n;
    }
    if (!$page->showFrontPage) {
      --$n;
    }
    return $n;
  }

  /**
   * Build altered breadcrumb items.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return array
   */
  function breadcrumbItems($page) {
    $breadcrumb_items = $page->rawBreadcrumbItems;
    if (empty($breadcrumb_items)) {
      return array();
    }
    $router_item = $this->router->getRouterItem($page->path);
    // Allow modules to alter the breadcrumb, if possible, as that is much
    // faster than rebuilding an entirely new active trail.
    drupal_alter('menu_breadcrumb', $breadcrumb_items, $router_item);

    // Prepare the items for easier theming.
    foreach ($breadcrumb_items as $i => &$item) {
      $item += array(
        'localized_options' => array(),
      );
      $item['localized_options'] += array('attributes' => array());
      $item['localized_options']['attributes'] += array('class' => '');
    }

    // Mark the last item.
    if (!empty($item) && $page->showCurrentPage) {
      $item['localized_options']['attributes']['class'] .= ' active crumbs-current-page';
      $item['crumbs_current_page'] = $page->showCurrentPage;
      switch ($page->showCurrentPage) {
        case CRUMBS_SHOW_CURRENT_PAGE_LINK:
          // Do nothing.
          break;
        case CRUMBS_SHOW_CURRENT_PAGE_SPAN:
          // Render as span.
          $item['href'] = '<nolink>';
          break;
        default:
          // Render without html tags around.
          $item['href'] = '<nolink>';
          $item['show_span'] = FALSE;;
      }
    }

    // Process <nolink> items.
    foreach ($breadcrumb_items as $i => &$item) {
      if ('<nolink>' === $item['href']) {
        $item['localized_options']['attributes']['class'] .= ' crumbs-nolink';
        $item += array('show_span' => TRUE);
      }
    }

    return $breadcrumb_items;
  }

  /**
   * Build the breadcrumb HTML.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return string
   */
  function breadcrumbHtml($page) {
    $breadcrumb_items = $page->breadcrumbItems;
    if (empty($breadcrumb_items)) {
      return '';
    }

    // Theme hook for breadcrumb links.
    $theme_hook = 'crumbs_breadcrumb_link';
    if (!empty($page->richSnippetMode)) {
      $theme_hook .= '__' . $page->richSnippetMode;
    }

    // Render each link separately.
    $links = array();
    foreach ($breadcrumb_items as $i => $item) {
      $links[$i] = theme($theme_hook, $item);
    }

    // Attributes for the container element.
    $attributes = array('class' => 'breadcrumb');
    if ('rdfa' === $page->richSnippetMode) {
      $attributes['xmlns:v'] = 'http://rdf.data-vocabulary.org/#';
    }

    // Render the complete breadcrumb.
    return theme('breadcrumb', array(
      'breadcrumb' => $links,
      'crumbs_breadcrumb_items' => $breadcrumb_items,
      'crumbs_trail' => $page->trail,
      'crumbs_separator' => $page->separator,
      'crumbs_separator_span' => $page->separatorSpan,
      'crumbs_trailing_separator' => $page->trailingSeparator,
      'attributes' => $attributes,
    ));
  }

  /**
   * Determine current path.
   *
   * @param crumbs_Container_LazyPageData $page
   * @return string
   */
  function path($page) {
    return $_GET['q'];
  }
}
