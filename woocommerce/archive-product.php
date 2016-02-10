/**
 * @version     2.0.0
 */

{block content}

	{doAction woocommerce_before_main_content}

	{doAction woocommerce_archive_description}

	{if $wp->havePosts}

		{doAction woocommerce_before_shop_loop}

		{? woocommerce_product_loop_start()}

		{? woocommerce_product_subcategories()}

		{loop as $p}
			{? wc_get_template_part( 'content', 'product' )}
		{/loop}

		{? woocommerce_product_loop_end()}

		{doAction woocommerce_after_shop_loop}

	{elseif !woocommerce_product_subcategories(array('before' => woocommerce_product_loop_start(false), 'after' => woocommerce_product_loop_end(false)))}

		{? wc_get_template( 'loop/no-products-found.php' )}

	{/if}

	{doAction woocommerce_after_main_content}
