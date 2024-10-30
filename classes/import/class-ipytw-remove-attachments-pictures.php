<?php if (!defined('ABSPATH')) {exit;}
/**
* Remove attachments pictures
*
* @link		https://icopydoc.ru/
* @since	1.9.0
*/

class IPYTW_Remove_Attachments_Pictures {
	public $additional_checks = 'disabled'; // Пока выключил. Дополнительная проверка на использование вложения в других постах

	public function __construct() {

	}

	/**
	 * @since 1.0.0
	 * 
	 * Get the list of attachments for the post we are about to delete and if the attachments 
	 * are not reused, remove them.
	 *
	 * @param int $post_id ID (require)
	 */
	public function remove_attachments($post_id) {
		// Force type as int.
		$post_id = (int)$post_id;

		$args  = array(
			'post_type'					=> 'attachment',
			'post_parent'				=> $post_id,
			'post_status'				=> 'any',
			'posts_per_page'			=> -1,
			'nopaging'					=> true,

			// Optimize query for performance.
			'no_found_rows'				=> true,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
		);
		$query = new WP_Query( $args );

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				switch ($this->additional_checks) {
					case 'enabled':
						$attachment_used_in = $this->get_attachment_used_in( $query->post->ID );

						// Remove current parent ID and normalize array.
						if (in_array( $post_id, $attachment_used_in, true)) {
							unset($attachment_used_in[array_search($post_id, $attachment_used_in, true)]);
							$attachment_used_in = array_values($attachment_used_in);  // Make consecutive keys.
						}

						// Change the parent ID if the attachment is reused. Delete otherwise.
						if (!empty($attachment_used_in)) {
							$args = array(
								'ID'			=> $query->post->ID,
								'post_parent'	=> $attachment_used_in[0],
							);
							wp_update_post($args);
						} else {
							wp_delete_attachment($query->post->ID, true);
						}
						break;

					default:
						wp_delete_attachment($query->post->ID, true);
						break;
				}
			}
		}
		wp_reset_postdata();
	}

	/**
	 * @since 1.0.0
	 * 
	 * Find where the attachment is used.
	 *
	 * Find where the attachment is used and return an array with all the IDs of posts, pages and custom post types 
	 * that use it either as a Featured Image, or inline, in the main content.
	 *
	 * @param int $attachment_id (require)
	 */
	public function get_attachment_used_in($attachment_id) {
		$attachment_used_in = array();
		$attachment_urls    = array(
			wp_get_original_image_url($attachment_id),
			wp_get_attachment_url($attachment_id),
		);
		$attachment_urls    = array_filter($attachment_urls);    // Remove empty values caused by wp_get_original_image_url() when the attachment is not an image.

		// If the attachment is an image, find where it's used as a Featured Image.
		if ( wp_attachment_is_image( $attachment_id ) ) {
			$args = array(
				'post_type'					=> 'any',
				'post_status'				=> array( 'any', 'publish', 'private', 'pending', 'future', 'draft'),
				'meta_key'					=> '_thumbnail_id', // phpcs:ignore
				'meta_value'				=> $attachment_id, // phpcs:ignore
				'posts_per_page'			=> -1,
				'nopaging'					=> true,
				'fields'					=> 'ids',

				// Optimize query for performance.
				'no_found_rows'				=> true,
				'update_post_meta_cache'	=> false,
				'update_post_term_cache'	=> false,
			);
			$query = new WP_Query($args);

			$attachment_used_in = array_merge($attachment_used_in, $query->posts);
		}

		// If the attachment is an image, find the URLs for all intermediate sizes.
		if ( wp_attachment_is_image( $attachment_id ) ) {
			foreach ( get_intermediate_image_sizes() as $size ) {
				$intermediate_size = image_get_intermediate_size( $attachment_id, $size );

				if ( $intermediate_size ) {
					$attachment_urls[] = $intermediate_size['url'];
				}
			}
		}

		// Find where the attachment URLs are used.
		foreach ( $attachment_urls as $attachment_url ) {
			$args = array(
				'post_type'					=> 'any',
				'post_status'				=> array('any', 'publish', 'private', 'pending', 'future', 'draft'),
				's'							=> $attachment_url,
				'posts_per_page'			=> -1,
				'nopaging'					=> true,
				'fields'					=> 'ids',

				// Optimize query for performance.
				'no_found_rows'				=> true,
				'update_post_meta_cache'	=> false,
				'update_post_term_cache'	=> false,
			);
			$query = new WP_Query($args);

			$attachment_used_in = array_merge($attachment_used_in, $query->posts);
		}

		// Check if the attachment is used in a WooCommerce gallery.
		$args = array(
			'post_type'					=> 'product',
			'post_status'				=> array('any', 'publish', 'private', 'pending', 'future', 'draft'),
			'posts_per_page'			=> -1,
			'nopaging'					=> true,

			// Optimize query for performance.
			'no_found_rows'				=> true,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
		);
		$query = new WP_Query( $args );

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$product				= wc_get_product($query->post->ID);
				$gallery_attachments	= $product->get_gallery_image_ids();
				if (in_array( $attachment_id, $gallery_attachments, true)) {
					$attachment_used_in[] = $query->post->ID;
				}
			}
		}

		// Normalize array before returning.
		$attachment_used_in = array_unique($attachment_used_in); // Keep unique values only.
		$attachment_used_in = array_filter($attachment_used_in); // Remove empty values.
		$attachment_used_in = array_values($attachment_used_in); // Make consecutive keys.

		// Return an array with all post IDs where the attachment is used.
		return $attachment_used_in;
	}
}