<?php if (!defined('ABSPATH')) {exit;}
/**
* Create Product Attributes
*
* @link			https://icopydoc.ru/
* @since		1.0.0
* https://question-it.com/questions/3093523/woocommerce-programmno-dobavljaet-atributy-produkta-i-ih-sootvetstvujuschie-znachenija
*/
/*
*	array(
*		array('name' => 'Производитель', 'values' => array('Адидас', 'Найк')),
*		array('name' => 'Страна', 'values' => array('Россия'))
*	),
*/
final class IPYTW_Import_Create_Product_Attributes {
	private $attributes_arr;
	private $post_id;

	public function __construct($args_arr, $post_id = null) {			
		$this->attributes_arr = $args_arr;
		if ($post_id) {$this->post_id = $post_id;}
	}

	public function create($is_variation_attr = true) {
		$attribs = $this->generate_attributes_list_for_product($this->get_attributes_arr(), $is_variation_attr);

		// Attribute Terms: These need to be set otherwise the attributes dont show on the admin backend:
		foreach ($attribs as $attrib) {
			$pa_tax = $attrib->get_name();
			$vals = $attrib->get_options();
			$terms_to_add_arr = array();

			if (is_array($vals) && count($vals) > 0) {
				foreach ($vals as $val) {
					// Get or create the term if it doesnt exist:
					$term = $this->get_attribute_term($val, $pa_tax);
					if ($term['id']) $terms_to_add_arr[] = $term['id'];
				}
			}

			/*
			if ($this->get_post_id() > 0) { // если переда id поста, то необходимо вернуть данные ввиде массива
				if (count($terms_to_add_arr) > 0) {
					wp_set_object_terms($this->get_post_id(), $terms_to_add_arr, $pa_tax, true);
				}
			}
			*/
		}
		return $attribs;
	}
  
	private function get_attribute_term($value, $taxonomy) {
		// Look if there is already a term for this attribute?
		$term = get_term_by('name', $value, $taxonomy);
		if (!$term) {
			// No, create new term.
			$term = wp_insert_term($value, $taxonomy);
			if (is_wp_error($term)) {
				//logg("Unable to create new attribute term for ".$value." in tax ".$taxonomy."! ".$term->get_error_message());
				return array('id' => false, 'slug' => false);
			}
			$termId = $term['term_id'];
			$term_slug = get_term($termId, $taxonomy)->slug; // Get the term slug
		} else {
			// Yes, grab it's id and slug
			$termId = $term->term_id;
			$term_slug = $term->slug;
		}
		return array('id' => $termId, 'slug' => $term_slug);
	}

	public function generate_attributes_list_for_product($rawDataAttributes_arr, $is_variation_attr) {
		// $is_variation_attr - если атрибут вариативный то true иначе false
		$attributes = array();

		$pos = 0;

		for ($i = 0; $i < count($rawDataAttributes_arr); $i++) {
			$attribute_name = $rawDataAttributes_arr[$i]['name'];
			$values_arr = $rawDataAttributes_arr[$i]['values'];
			if (empty($attribute_name) || empty($values_arr)) {continue;}
			if (!is_array($values_arr)) {$values_arr = array($values_arr);}

			$attribute = new WC_Product_Attribute();
			$attribute->set_id( 0 );
			$attribute->set_position($pos);
			$attribute->set_visible( true );
			$attribute->set_variation( $is_variation_attr );

			$pos++;

			//Look for existing attribute:
			$existingTaxes = wc_get_attribute_taxonomies();

			// attribute_labels is in the format: array("slug" => "label / name")
			$attribute_labels = wp_list_pluck($existingTaxes, 'attribute_label', 'attribute_name');
			$attribute_slug = array_search( $attribute_name, $attribute_labels, true );

			if (!$attribute_slug) {
				// Not found, so create it:
				$attribute_slug = wc_sanitize_taxonomy_name($attribute_name);
				$attribute_slug = $this->do_valid_slug($attribute_slug);
				$attribute_id = $this->create_global_attribute($attribute_name, $attribute_slug);
			} else {
				// Otherwise find it's ID
				// Taxonomies are in the format: array("slug" => 12, "slug" => 14)
				$taxonomies = wp_list_pluck($existingTaxes, 'attribute_id', 'attribute_name');

				if (!isset($taxonomies[$attribute_slug])) {
					// logg("Could not get wc attribute ID for attribute ".$attribute_name. " (slug: ".$attribute_slug.") which should have existed!");
					continue;
				}

				$attribute_id = (int)$taxonomies[$attribute_slug];
			}

			// echo '$attribute_slug ====== '.$attribute_slug;

			$pa_attribute_name = wc_attribute_taxonomy_name($attribute_slug);

			$attribute->set_id( $attribute_id );
			$attribute->set_name( $pa_attribute_name );
			$attribute->set_options($values_arr);

			$attributes[] = $attribute;
		}
		return $attributes;
	}
	
	private function create_global_attribute($attribute_name, $attribute_slug = '') {
		if ($attribute_slug === '') {
			$attribute_slug = wc_sanitize_taxonomy_name($attribute_name); // приводим к виду второй-тест
		}

		$pa_attribute_name = wc_attribute_taxonomy_name($attribute_slug);

		if (taxonomy_exists($pa_attribute_name)) {
			$attribute_id = wc_attribute_taxonomy_id_by_name($attribute_slug);
			return $attribute_id;
		}

		$attribute_slug = $this->do_valid_slug($attribute_slug);
		
		if (wc_check_if_attribute_name_is_reserved($attribute_slug)) {
			new IPYTW_Error_Log('ERROR: Ошибка создания атрибута. Название таксономии $attribute_slug = '.$attribute_slug.' зарезервировано; Файл: function.php; Строка: '.__LINE__);
			return false;
		}

		$attribute_id = wc_create_attribute( array(
			'name'			=> $attribute_name,
			'slug'			=> $attribute_slug,
			'type'			=> 'select',
			'order_by'		=> 'menu_order',
			'has_archives'	=> false,
		) );

		// Register it as a wordpress taxonomy for just this session. Later on this will be loaded from the woocommerce taxonomy table.
		register_taxonomy(
			$pa_attribute_name,
			apply_filters( 'woocommerce_taxonomy_objects_' . $pa_attribute_name, array( 'product' ) ),
			apply_filters( 'woocommerce_taxonomy_args_' . $pa_attribute_name, array(
				'labels'	=> array(
					'name' => $attribute_name,
				),
				'hierarchical'	=> true,
				'show_ui'		=> false,
				'query_var'		=> true,
				'rewrite'		=> false,
			) )
		);
	
		delete_transient('wc_attribute_taxonomies'); // Clear caches
	
		return $attribute_id;
	}

	private function do_valid_slug($attribute_slug) {
		$attribute_slug = translit_cyr_en($attribute_slug);
		if (strlen($attribute_slug) >= 28) {
			$attribute_slug = mb_substr($attribute_slug, 0, 27); // 27 символом на слаг
			if (strlen($attribute_slug) >= 28) {	// если в итоге байт больше
				for ($i = mb_strlen($attribute_slug); $i = 1; $i--) {
					$attribute_slug = mb_substr($attribute_slug, 0, -1);
					if (strlen($attribute_slug) < 28) {break;}
				}
			}
		}
		return $attribute_slug; 
	}

	private function get_attributes_arr() {
		return $this->attributes_arr; 
	}

	private function get_post_id() {
		return $this->post_id; 
	}
}
?>