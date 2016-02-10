<?php
/**
 * Single Product title
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

global $product;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$average = $product->get_average_rating();

?>
<h1 itemprop="name" class="product_title entry-title"><?php the_title(); ?></h1>