<?php

function aitGetItems($args, $cacheKey = '')
{
    static $_query;
    if (!empty($cacheKey)) {
        if(!is_null($_query[$cacheKey])){
            return $_query[$cacheKey];
        }
        else {
            $_query[$cacheKey] = new WpLatteWpQuery($args);
            return $_query[$cacheKey];
        }
    } else {
        return new WpLatteWpQuery($args);

    }
}



function aitAddAdvancedFiltersArgs($args, $filters)
{
    $filters = explode(";", $filters);
    $args['meta_query'] = aitFiltersMetaQuery($filters);
    return $args;
}



function aitAlterRadiusArgs($args, $geoRadiusValue, $geoLat, $geoLon, $postType = 'ait-item')
{
    switch ($postType) {
        case 'ait-item':
           $metaKey = 'item-data';
            break;
        case 'ait-event-pro':
           $metaKey = 'event-pro-data';
            break;
        default:
            break;
    }

    $filtered = array();
    $query = aitGetItems($args);
    foreach (new WpLatteLoopIterator($query) as $item) {
        $meta = $item->meta($metaKey);

        if (isPointInRadius($geoRadiusValue, $geoLat, $geoLon, $meta->map['latitude'], $meta->map['longitude'])){
            array_push($filtered, $item->id);
        }
    }
    if (empty($filtered)) {
        // no items in radius
        $args['post__in'] = array(0);
    } else {
        $args['post__in'] = $filtered;
    }

    return $args;
}








function getDefaultQueryArgs()
{
    $settings = aitOptions()->getOptionsByType('theme');
    $settings = $settings['items'];
    return array(
        'orderby' => $settings['sortingDefaultOrderBy'],
        'order' => $settings['sortingDefaultOrder'],
        );
}

// Join for searching metadata
function aitPostsJoin($join) {
    global $wp_query, $wpdb;

    $defaultQueryArgs = getDefaultQueryArgs();
    $orderBy = isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != "" ? $_REQUEST['orderby'] : $defaultQueryArgs['orderby'];

    switch ($orderBy) {
        case 'rating':
            $join .= "LEFT JOIN $wpdb->postmeta AS featuredTerm ON
                $wpdb->posts.ID = featuredTerm.post_id
                AND featuredTerm.meta_key = '_ait-item_item-featured'

            LEFT JOIN $wpdb->postmeta AS rating_mean ON
                $wpdb->posts.ID = rating_mean.post_id
                AND rating_mean.meta_key = 'rating_mean'";
            break;

        default:
            $join .= "LEFT JOIN $wpdb->postmeta AS featuredTerm ON $wpdb->posts.ID = featuredTerm.post_id AND featuredTerm.meta_key = '_ait-item_item-featured'";
            break;
    }

    return $join;
}



function aitPostsOrderby($orderby_statement) {
    global $wp_query, $wpdb;

    $defaultQueryArgs = getDefaultQueryArgs();
    $orderBy = isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != "" ? $_REQUEST['orderby'] : $defaultQueryArgs['orderby'];
    $order = isset($_REQUEST['order']) && $_REQUEST['order'] != "" ? $_REQUEST['order'] : $defaultQueryArgs['order'];

    // prevent other than ASC DESC values
    $orderstr = 'ASC';
    if (strtolower($order) == 'desc') {
        $orderstr = 'DESC';
    }

    switch ($orderBy) {
        case 'rating':
            $orderby_statement = "featuredTerm.meta_value DESC, rating_mean.meta_value ".$orderstr;
            break;
        case 'title':
            $orderby_statement = "featuredTerm.meta_value DESC, $wpdb->posts.post_title ".$orderstr;
            break;
        case 'date':
            $orderby_statement = "featuredTerm.meta_value DESC, $wpdb->posts.post_date ".$orderstr;
            break;
    }

    return $orderby_statement;
}



function aitFiltersMetaQuery($definedFilters)
{
    $metaQuery = array();
    foreach ($definedFilters as $key => $value) {
        array_push($metaQuery, array(
            'key' => '_ait-item_filters-options',
            'value' => '"'.$value.'"',
            'compare' => 'LIKE',
        ));
    }

    return $metaQuery;
}




function aitGetRelatedEvents($itemID)
{
    $args = array(
        'post_type' => 'ait-event-pro',
        'meta_key' => 'ait-event-pro-related-item',
        'meta_value' => $itemID,
    );
    return aitGetItems($args);
}



function aitEventAddress($event, $all = false)
{
    $meta = $event->meta('event-pro-data');
    $useItemLocation = filter_var($meta->useItemLocation, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($useItemLocation and !empty($meta->item)) {
        $itemMeta = get_post_meta($meta->item, '_ait-item_item-data', true);
        if ($all) {
            return array(
                'address'    => $itemMeta['map']['address'],
                'latitude'   => $itemMeta['map']['latitude'],
                'longitude'  => $itemMeta['map']['longitude'],
                'swheading'  => $itemMeta['map']['swheading'],
                'swpitch'    => $itemMeta['map']['swpitch'],
                'swzoom'     => $itemMeta['map']['swzoom'],
                'streetview' => $itemMeta['map']['streetview'],
            );
        }
        return $itemMeta['map']['address'];
    } else {
        if ($all) {
            return array(
                'address'   => $meta->map['address'],
                'latitude'  => $meta->map['latitude'],
                'longitude' => $meta->map['longitude'],
                'swheading' => $meta->map['swheading'],
                'swpitch' => $meta->map['swpitch'],
                'swzoom' => $meta->map['swzoom'],
                'streetview' => $meta->map['streetview'],
            );
        }
        return $meta->map['address'];
    }
}





function aitGetAllRecurringDates()
{
    global $wpdb;

    $query =
        "SELECT DATE(pm.meta_value) as 'meta_value' FROM $wpdb->postmeta pm
        LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'ait-event-recurring-date'
        AND p.post_status = 'publish'
        AND p.post_type = 'ait-event-pro'
        ORDER BY pm.meta_value ASC";
    $result = $wpdb->get_results( $query, ARRAY_A );
    $allMeta = array();
    foreach ($result as $key => $meta) {
        array_push($allMeta, $meta['meta_value']);
    }
    return $allMeta;
}
// getAllRecurringDates();


function aitGetNextDate($dates, $from = '', $includeToday = false)
{
    if (empty($dates)) {
        return array();
    }
    $now = empty($from) ? new DateTime() : new DateTime($from);
    $nowTimestamp = ($now->getTimeStamp());

    // if date is today consider also time
    // if ( date('Ymd') == date('Ymd', strtotime($dateSelected))) {
    //    $dateSelected = date('Y-m-d H:i:s');
    // }


    if (isset($dates[0]) && is_array( $dates[0] )) {
        // dates array consists of elements with dateFrom and dateTo
        foreach ($dates as $date) {
            $newDate = new DateTime($date['dateFrom']);
            $newDateTimestamp = ($newDate->getTimeStamp());
            if ($includeToday) {
                if ($newDateTimestamp >= $nowTimestamp) {
                    return $date;
                }
            } else {
                if ($newDateTimestamp > $nowTimestamp) {
                    return $date;
                }
            }

        }
    } else {
        // simple array of single dates
        foreach ($dates as $date) {
            $newDate = new DateTime($date);
            if ($includeToday) {
                if ($newDate > $now) {
                    return $date;
                }
            } else {
                if ($newDate > $now) {
                    return $date;
                }
            }

        }
    }

    return array();
}



function aitGetRecurringDates($event, $from = '')
{
    $result = array();
    $now = empty($from) ? new DateTime() : new DateTime($from);
    $now = $now->getTimeStamp();

    $meta = $event->meta('event-pro-data');

    $dates = $meta->dates;
    foreach ($dates as $date) {
       if (strtotime($date['dateFrom']) >= $now ) {
            array_push($result, $date);
       }
    }

    return $result;
}




function aitDoSearch()
{
    $postType = '';
    $s = $_REQUEST['s'];
    $metaQuery = array();
    $orderBy = array();
    $order = 'ASC';
    $taxQueries = array();
    $count = -1;
    $lang = AitLangs::getCurrentLanguageCode();
    $paged = get_query_var( 'paged', 1 );

    /******* SEARCH AIT *******/
    if (!empty($_REQUEST['a'])) {
        $taxonomy = (isset($_REQUEST['type']) and $_REQUEST['type'] == "events-pro") ? 'ait-events-pro' : 'ait-items';
        /* NOTE THAT CATTEGORY PARAMETER IS ARRAY AND IS NEVER EMPTY */
            // $taxQueries['relation'] = 'AND';
        if (!empty($_REQUEST['category'][0])) {
            array_push($taxQueries, array('taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $_REQUEST['category'], 'operator' => 'IN'));
        }

        /* locations are shared for items and events  */
        if (!empty($_REQUEST['location'])){
            // $taxQueries['relation'] = 'AND';
            array_push($taxQueries, array('taxonomy' => 'ait-locations', 'field' => 'term_id', 'terms' => $_REQUEST['location']));
        }


        if (!empty($_REQUEST['lang'])) {
            $lang = $_REQUEST['lang'];
        }

        $sortingSettings = aitOptions()->getOptionsByType('theme');
        $sortingSettings = $sortingSettings['sorting'];

        /******* SEARCH EVENTS-PRO *******/
        if (isset($_REQUEST['type']) and $_REQUEST['type'] == "events-pro") {
            $eventsProOptions = $eventOptions = get_option('ait_events_pro_options', array());
            $postType = 'ait-event-pro';
            $count = $eventsProOptions['sortingDefaultCount'];

            $dateSelected = !empty($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');

            // if date is today consider also time
            if ( date('Ymd') == date('Ymd', strtotime($dateSelected))) {
               $dateSelected = date('Y-m-d H:i:s');
            }


            $metaQuery = array(
                'dates_clause' => array(
                    'key'     => 'ait-event-recurring-date',
                    'value'   => $dateSelected,
                    'compare' => '>=',
                ),
            );
            $orderBySelected = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : $eventsProOptions['sortingDefaultOrderBy'];
            $orderSelected   = !empty($_REQUEST['order']) ? $_REQUEST['order'] : $eventsProOptions['sortingDefaultOrder'];
            if ($orderBySelected == 'date') {
                $orderBy = array(
                    'dates_clause' => $orderSelected,
                );
            }else {
                $orderBy = $orderBySelected;
                $order = $orderSelected;
            }

        /******* SEARCH ITEMS *******/
        } else {

            $topFeatured = $sortingSettings['topFeatured'];
            $postType = 'ait-item';
            $count = $sortingSettings['sortingDefaultCount'];
            $orderBySelected = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : $sortingSettings['sortingDefaultOrderBy'];
            $orderSelected   = !empty($_REQUEST['order']) ? $_REQUEST['order'] : $sortingSettings['sortingDefaultOrder'];

            /******* PUSH FEATURED ON TOP POSITIONS *******/
            if ($topFeatured) {
                $metaQuery = array(
                    'relation'        => 'AND',
                    'featured_clause' => array(
                        'key'     => '_ait-item_item-featured',
                        'compare' => 'EXISTS'
                    )
                );
                $orderBy['featured_clause'] = 'DESC';
            }

            if (defined('AIT_REVIEWS_ENABLED') and $orderBySelected == 'rating') {
                $metaQuery['rating_clause'] = array(
                    'key' => 'rating_mean',
                    'compare' => 'EXISTS'
                );
                $orderBy['rating_clause'] = $orderSelected;
            }

            $orderBy[$orderBySelected] = $orderSelected;
        }

    /******* SEARCH POSTS AND PAGES *******/
    } else {
        $postType = array('post', 'page');
        $orderBy['date'] = 'DESC';
    }

    $args = array(
        'lang'           => $lang,
        'post_type'      => $postType,
        'posts_per_page' => $count,
        's'              => $s,
        'meta_query'     => $metaQuery,
        'tax_query'      => $taxQueries,
        'orderby'        => $orderBy,
        'paged'          => $paged,
    );

    /******* FILTER BY RADIUS *******/
    if (!empty($_REQUEST['lat']) && !empty($_REQUEST['lon']) and !empty($_REQUEST['rad'])) {
        $geoRadiusUnits = !empty($_REQUEST['runits']) ? $_REQUEST['runits'] : 'km';
        $geoRadiusValue = !empty($_REQUEST['rad']) ? $_REQUEST['rad'] * 1000 : 100 * 1000;
        $geoRadiusValue = $geoRadiusUnits == 'mi' ? $geoRadiusValue * 1.609344 : $geoRadiusValue;

        $geoLat = $_REQUEST['lat'];
        $geoLon = $_REQUEST['lon'];

        $args = aitAlterRadiusArgs($args, $geoRadiusValue, $geoLat, $geoLon, $postType);
    }
    return aitGetItems($args, 'main-search');
}




function aitItemRelatedEvents($itemId, $args = array())
{
    $eventsProOptions = get_option('ait_events_pro_options', array());
    $defaults = array(
        'posts_per_page' => $eventsProOptions['sortingDefaultCount'],
        'orderby' => $eventsProOptions['sortingDefaultOrderBy'],
    );

    $args = wp_parse_args($args, $defaults);

    $sortingSettings = $settings = aitOptions()->getOptionsByType('theme');
    $sortingSettings = $sortingSettings['items'];

    // eventguide has different theme options
    // $sortingSettings = $sortingSettings['sorting'];


    $order = $sortingSettings['sortingDefaultOrder'];
    $orderBy = array();

    if ($orderBy == 'date') {
        $orderBy = array('dates_clause' => $order);
    }


    $orderBy[$args['orderby']] = $order;

    $eventsArgs = array(
        'post_type'      => 'ait-event-pro',
        'posts_per_page' => $args['posts_per_page'],
        'lang'           => AitLangs::getCurrentLanguageCode(),
        'meta_query' => array(
            'relation' => 'AND',
            'dates_clause' => array(
                'key'     => 'ait-event-recurring-date',
                'value'   => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'date',
            ),
            'related_clause' => array(
                'key'     => 'ait-event-pro-related-item',
                'value'   => $itemId,
                'compare' => '=',
            )
        ),
        'orderby' => $orderBy,
    );
    return aitGetItems($eventsArgs, 'single-item-related-events');
}


// determine the topmost parent of a term
function aitGetOnlyParentTerms($postId, $taxonomy){
    $terms = get_the_terms($postId, $taxonomy);
    $categoryParent = '';
    $counter = 0;
    if ($terms) {
        foreach ($terms as $category) {
            // start from the current term
            $parent  = get_term_by( 'id', $category->term_id, 'ait-events-pro');
            // climb up the hierarchy until we reach a term with parent = '0'
            while ($parent->parent != '0'){
                $term_id = $parent->parent;

                $parent  = get_term_by( 'id', $term_id, 'ait-events-pro');
            }
            $parents[$parent->term_id] = new WpLatteTaxonomyTermEntity($parent, 'ait-events-pro');
            // $categories = $wp->categories(array('taxonomy' => 'ait-events-pro', 'hide_empty' => 0, 'parent' => $parentCategory))}
        }
    }
    return $parents;
}