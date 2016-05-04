<?php
// allows programmers to include part in the same way as in latte syntax
function aitRenderLatteTemplate($template, $params = array())
{
    AitWpLatte::init();
    ob_start();
    WpLatte::render(aitPath('theme', $template), $params);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}



function aitGetItemsMarkers($query)
{
    $markers = array();

    foreach (new WpLatteLoopIterator($query) as $item) {
        $meta = $item->meta('item-data');
        // skip items with [1,1] coordinates
        if ($meta->map['latitude'] == 1 and $meta->map['longitude'] == 1) {
            continue;
        }

        $price = get_post_meta( $item->id, '_ait-item_food-options_price', true );

        $options = aitOptions()->getOptionsByType('theme');
		$featuredImage = $item->imageUrl;
        $imageLink = empty($featuredImage) ? $options['item']['noFeatured'] : $item->imageUrl;

        $context = aitRenderLatteTemplate('/parts/item-marker.php', array('item' => $item, 'meta' => $meta));
        $catData = aitItemCategoriesData($item->id);
        $marker = (object)array(
            'lat'        => $meta->map['latitude'],
            'lng'        => $meta->map['longitude'],
            'title'      => $item->rawTitle,
            'icon'       => $catData['icon'],
            'context'    => $context,
            'type'       => 'item',
            'data'       => array(),
        );
        array_push($markers, $marker);
    }
    return $markers;
}

function aitItemCategoriesData($itemID)
{
    $options = aitOptions()->getOptionsByType('theme');
    $itemCats = get_the_terms($itemID, 'ait-items');
    $icon = $options['items']['categoryDefaultPin'];
    // $parents = array();

    if (!$itemCats) {
        $itemCats = array();
    }

    foreach ($itemCats as $cat) {
        $parent = get_term($cat->parent, 'ait-items');
        $catOption = get_option('ait-items_category_'.$cat->term_id);

        if (!empty($catOption['map_icon'])) {
            $icon = $catOption['map_icon'];
        } elseif(isset($parent) && !($parent instanceof WP_Error)) {
            $parentOption = get_option('ait-items_category_'.$parent->term_id);
            // array_push($parents, $parent->term_id);
            if (!empty($parentOption['map_icon'])) {
                $icon = $parentOption['map_icon'];
            }
        }

        // if(isset($parent) && !($parent instanceof WP_Error) && !in_array($parent->term_id, $parents)) {
        //     array_push($parents, $parent->term_id);
        // } else {
        //     array_push($parents, $cat->term_id);
        // }
    }

    return array(
        'icon' => $icon,
        // 'parents' => $parents
    );
}

function aitGetMapOptions($options)
{
    $result = array();
    $result['styles'] = aitGetMapStyles($options);

    if (!isset($options['autoZoomAndFit']) || !$options['autoZoomAndFit']) {
        $result['center'] = array(
            'lat' => floatval($options['address']['latitude']),
            'lng' => floatval($options['address']['longitude']),
        );
    }

    if (!empty($options['mousewheelZoom'])) {
        $result['scrollwheel'] = true;
    }

    if (isset($options['zoom'])) {
        $result['zoom'] = intval($options['zoom']);
    }

    return $result;
}

function aitGetMapStyles($options)
{
    $o = $options;
    $styles = array(
    	array(
    		'stylers' => array(
                array('hue'        => $o['mapHue']),
                array('saturation' => $o['mapSaturation']),
                array('lightness'  => $o['mapBrightness']),
    		),
    	),
    	array(
    		'featureType' => 'landscape',
    		'stylers' => array(
                array('visibility' => $o['landscapeShow'] == false ? 'off' : 'on'),
                array('hue'        => $o['landscapeColor']),
                array('saturation' => $o['landscapeColor'] != '' ? $o['objSaturation'] : ''),
                array('lightness'  => $o['landscapeColor'] != '' ? $o['objBrightness'] : ''),
    		),
    	),
        array(
            'featureType' => 'administrative',
            'stylers' => array(
                array('visibility' => $o['administrativeShow'] == false ? 'off' : 'on'),
                array('hue'        => $o['administrativeColor']),
                array('saturation' => $o['administrativeColor'] != '' ? $o['objSaturation'] : ''),
                array('lightness'  => $o['administrativeColor'] != '' ? $o['objBrightness'] : ''),
            ),
        ),
        array(
            'featureType' => 'road',
            'stylers' => array(
                array('visibility' => $o['roadsShow'] == false ? 'off' : 'on'),
                array('hue'        => $o['roadsColor']),
                array('saturation' => $o['roadsColor'] != '' ? $o['objSaturation'] : ''),
                array('lightness'  => $o['roadsColor'] != '' ? $o['objBrightness'] : ''),
            ),
        ),
        array(
            'featureType' => 'water',
            'stylers' => array(
                array('visibility' => $o['waterShow'] == false ? 'off' : 'on'),
                array('hue'        => $o['waterColor']),
                array('saturation' => $o['waterColor'] != '' ? $o['objSaturation'] : ''),
                array('lightness'  => $o['waterColor'] != '' ? $o['objBrightness'] : ''),
            ),
        ),
        array(
            'featureType' => 'poi',
            'stylers' => array(
                array('visibility' => $o['poiShow'] == false ? 'off' : 'on'),
                array('hue'        => $o['poiColor']),
                array('saturation' => $o['poiColor'] != '' ? $o['objSaturation'] : ''),
                array('lightness'  => $o['poiColor'] != '' ? $o['objBrightness'] : ''),
            ),
        ),
    );
    return $styles;
}


add_filter( 'ait_alter_search_query', function($query){
    /* VARIABLES */
    $settings = aitOptions()->getOptionsByType('theme');
    $settings = (object)$settings['items'];
    $filterCountsSelected = isset($_REQUEST['count']) && $_REQUEST['count'] != "" ? $_REQUEST['count'] : $settings->sortingDefaultCount;
    $filterOrderBySelected = isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != "" ? $_REQUEST['orderby'] : $settings->sortingDefaultOrderBy;
    $filterOrderSelected = isset($_REQUEST['order']) && $_REQUEST['order'] != "" ? $_REQUEST['order'] : $settings->sortingDefaultOrder;

    $taxQuery  = array();
    $metaQuery = array(
        'relation' => 'AND',
        'featured_clause' => array(
            'key'   => '_ait-item_item-featured',
            'compare' => 'EXISTS'
        )
    );

     if (defined('AIT_REVIEWS_ENABLED') and $filterOrderBySelected == 'rating') {
        $metaQuery['rating_clause'] = array(
            'key' => 'rating_mean',
            'compare' => 'EXISTS'
        );
        $filterOrderBySelected = 'rating_clause';
    }

    /* APPLY ADVANCED FILTERS */
    if (!empty($_REQUEST['filters'])) {
        $metaQuery = aitFilterByAdvancedFilters( $metaQuery, $_REQUEST['filters'] );
    }

    /* IS SEARCH PAGE */
    if(isset($_REQUEST['a']) && $_REQUEST['a'] == true) {

        $query->set('post_type', 'ait-item');

        /* FILTER BY TAXONOMIES */
        if (!empty($_REQUEST['category'])) {
            array_push($taxQuery, array('taxonomy' => 'ait-items', 'field' => 'term_id', 'terms' => $_REQUEST['category']));
        }
        if (!empty($_REQUEST['location'])){
            array_push($taxQuery, array('taxonomy' => 'ait-locations', 'field' => 'term_id', 'terms' => $_REQUEST['location']));
        }


        /* FILTER BY RADIUS */
        if (!empty($_REQUEST['lat']) && !empty($_REQUEST['lon']) and !empty($_REQUEST['rad'])) {
            $radiusUnits = !empty($_REQUEST['runits']) ? $_REQUEST['runits'] : 'km';
            $radiusValue = !empty($_REQUEST['rad']) ? $_REQUEST['rad'] * 1000 : 100 * 1000;
            $radiusValue = $radiusUnits == 'mi' ? $radiusValue * 1.609344 : $radiusValue;

            $latitude = $_REQUEST['lat'];
            $longitude = $_REQUEST['lon'];


            $query->set('post_type', 'ait-item');
            $query->set('posts_per_page', -1);
            $query->set('meta_query', $metaQuery);
            $query->set('tax_query', $taxQuery);

            $queryToFilter = new WP_Query($query->query_vars);
            $filteredByRadiusList = aitFilterByRadius($queryToFilter, $radiusValue, $latitude, $longitude);

            /* if $filteredByRadiusList is empty it means there are no items in result */
            if (empty($filteredByRadiusList)) {
               $filteredByRadiusList = array(0);
            }

            wp_reset_query();
            $query->set('post__in', $filteredByRadiusList);
        } else {
            $query->set('meta_query', $metaQuery);
            $query->set('tax_query', $taxQuery);
            // $query->set('post__in', $itemsList);
        }
        //$query->set('s', ''); ? why setting this empty ? this is keyword
        $query->set('post_type', 'ait-item');
        $query->set('posts_per_page', $filterCountsSelected);
        $query->set('orderby', array(
            'featured_clause' => 'DESC',
            $filterOrderBySelected => $filterOrderSelected
        ));

    /* IS TAXONOMY PAGE */
    } elseif ($query->is_tax('ait-items') || $query->is_tax('ait-locations') || is_post_type_archive('ait-item')) {
        $query->set('posts_per_page', $filterCountsSelected);
        $query->set('meta_query', $metaQuery);
        $query->set('orderby', array(
            'featured_clause' => 'DESC',
            $filterOrderBySelected => $filterOrderSelected
        ));
    }
    return $query;
} );



function aitFilterByRadius($query, $radiusValue, $latitude, $longitude)
{
    $metaKey = 'item-data';

    $filtered = array();
    foreach (new WpLatteLoopIterator($query) as $item) {
        $meta = $item->meta($metaKey);
        $lat = !empty($meta->map['latitude']) ? $meta->map['latitude'] : false;
        $lng = !empty($meta->map['longitude']) ? $meta->map['longitude'] : false;

        if($lat !== false && $lng !== false){
            if (isPointInRadius($radiusValue, $latitude, $longitude, $lat, $lng)){
                array_push($filtered, $item->id);
            }
        }
    }
    return $filtered;
}



function aitFilterByAdvancedFilters($metaQuery, $filters)
{
    $filters = explode(";", $filters);

    foreach ($filters as $key => $value) {
        array_push($metaQuery, array(
            'key' => '_ait-item_filters-options',
            'value' => '"'.$value.'"',
            'compare' => 'LIKE',
        ));
    }

    return $metaQuery;
}



add_action( 'save_post', 'aitSaveItemMeta', 13, 2 );
function aitSaveItemMeta( $post_id, $post )
{
    $slug = 'ait-item';

    if ( $slug != $post->post_type ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Prevent quick edit from clearing custom fields
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // save separated meta data for featured
    if(isset($_POST['_ait-item_item-data']['featuredItem'])){
        if (intval($_POST['_ait-item_item-data']['featuredItem']) == 1) {
            update_post_meta($post_id, '_ait-item_item-featured', '1');
        }
        else {
            update_post_meta($post_id, '_ait-item_item-featured', '0');
        }
    } else {
        // item created with directory role that cannot set item as featured
        update_post_meta($post_id, '_ait-item_item-featured', '0');
    }

    // if item hasn't been rated yet, create rating manually
    if (get_post_meta( $post_id, 'rating_mean', true ) == '') {
        update_post_meta($post_id, 'rating_mean', '0');
    }
}




