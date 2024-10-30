<?php
/**
 * Import images
 *
 * @package                 Import from YML
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 3.1.3 (28-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see           
 * 
 * @param     string        $feed_picture_url
 * @param     array         $args
 * @param     string        $feed_id
 *
 * @depends                 classes:    IPYTW_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  ipytw_optionGET
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class IPYTW_Download_Pictures {
	/**
	 * Summary of feed_picture_url
	 * @var string
	 */
	private $feed_picture_url;
	/**
	 * Feed ID
	 * @var string
	 */
	private $feed_id;
	/**
	 * Summary of name
	 * @var string
	 */
	private $name;
	/**
	 * Summary of image_id
	 * @var int
	 */
	private $image_id = -1;

	/**
	 * Import images
	 * 
	 * @param string $feed_picture_url
	 * @param array $args
	 * @param string $feed_id
	 */
	public function __construct( $feed_picture_url, $args, $feed_id ) {
		$this->feed_picture_url = $feed_picture_url;
		$this->name = $args['name'];
		$this->feed_id = $feed_id;
		$this->import();
	}

	/**
	 * Summary of get_image_id
	 * 
	 * @return WP_Error|int
	 */
	public function get_image_id() {
		return $this->image_id;
	}

	/**
	 * Run image import
	 * 
	 * @return void
	 */
	private function import() {
		$ipytw_fsize_limit = ipytw_optionGET( 'ipytw_fsize_limit', $this->get_feed_id(), 'set_arr' );
		$fsize_size = ipytw_fsize( $this->get_feed_picture_url(), 'MB', 'no', 0 );
		if ( $ipytw_fsize_limit > $fsize_size ) { // размер файла в пределах лимита
			new IPYTW_Error_Log( 'FEED № ' . $this->get_feed_id() . '; $ipytw_fsize_limit = ' . $ipytw_fsize_limit . ' > $fsize_size = ' . $fsize_size . '; Файл: class-ipytw-download-pictures.php; Строка: ' . __LINE__ );
			preg_match( "/^(?:.*\b(\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG|\.gif|\.GIF|\.webp|\.WEBP))\b/", $this->get_feed_picture_url(), $match_arr );
			if ( ! empty( $match_arr ) ) {
				$feed_picture_url2 = strstr( $this->get_feed_picture_url(), $match_arr[1], true );
				$filename = basename( urldecode( $feed_picture_url2 . $match_arr[1] ) );
			} else {
				$filename = basename( urldecode( $this->get_feed_picture_url() ) );
			}
			if ( ! empty( $filename ) ) {
				$upload_file = wp_upload_bits( $filename, null, ipytw_file_get_contents_curl( $this->get_feed_picture_url() ) );
				if ( ! $upload_file['error'] ) {
					$wp_filetype = wp_check_filetype( $filename, null );
					$attachment = [ 
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => $this->get_name(),
						'post_content' => '',
						'post_status' => 'inherit'
					];
					$image_id = wp_insert_attachment( $attachment, $upload_file['file'] );
					new IPYTW_Error_Log(
						'FEED № ' . $this->get_feed_id() . '; wp_insert_attachment =>; Файл: class-ipytw-download-pictures.php; Строка: ' . __LINE__
					);
					if ( ! is_wp_error( $image_id ) ) {
						new IPYTW_Error_Log(
							'FEED № ' . $this->get_feed_id() . '; $image_id = ' . $image_id . '; Файл: class-ipytw-download-pictures.php; Строка: ' . __LINE__
						);
						require_once ( ABSPATH . "wp-admin" . '/includes/image.php' );
						$attachment_data = wp_generate_attachment_metadata( $image_id, $upload_file['file'] );
						wp_update_attachment_metadata( $image_id, $attachment_data );
						update_post_meta( $image_id, '_ipytw_import_feed_picture_url', $this->get_feed_picture_url() );
						$this->image_id = $image_id;
					} else {
						new IPYTW_Error_Log(
							'FEED № ' . $this->get_feed_id() . '; ERROR: Ошибка при загрузке картинки; Файл: class-ipytw-download-pictures.php; Строка: ' . __LINE__
						);
						new IPYTW_Error_Log( $image_id, 0 );
					}
				}
			}
		} else { // размер файла в пределах лимита
			new IPYTW_Error_Log(
				'FEED № ' . $this->get_feed_id() . '; Размер картинки выше лимита. Пропускаем картинку $ipytw_fsize_limit = ' . $ipytw_fsize_limit . '; $fsize_size = ' . $fsize_size . '; Файл: class-ipytw-download-pictures.php; Строка: ' . __LINE__
			);
		}
	}

	/**
	 * Summary of get_feed_picture_url
	 * 
	 * @return string
	 */
	private function get_feed_picture_url() {
		return $this->feed_picture_url;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Summary of get_name
	 * 
	 * @return string
	 */
	private function get_name() {
		return $this->name;
	}
}