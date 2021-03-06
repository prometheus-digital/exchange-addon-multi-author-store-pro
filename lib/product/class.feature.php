<?php

/**
 * Class ITEMAP_Product_Feature
 */
class ITEMAP_Product_Feature extends IT_Exchange_Product_Feature_Abstract {

	/**
	 * Register our multi-author product feature.
	 *
	 * @param array $args
	 */
	function __construct( $args = array() ) {
		parent::IT_Exchange_Product_Feature_Abstract( $args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @param $post WP_Post
	 */
	function print_metabox( $post ) {

		$users = itemap_get_product_authors();
		?>
		<p><?php echo $this->description; ?></p>

		<label for="ibd_author_select">
			<?php _e( 'Select the author', ITEMAP::SLUG ); ?>
		</label>
		<select id="ibd_author_select" name="ibd_author_select">
			<?php foreach ( $users as $user ): ?>
				<?php
				if ( empty( $user->last_name ) && empty( $user->first_name ) ) {
					$name = $user->display_name;
				} elseif ( empty( $user->last_name ) ) {
					$name = $user->first_name;
				} elseif ( empty( $user->first_name ) ) {
					$name = $user->last_name;
				} else {
					$name = $user->last_name . ', ' . $user->first_name;
				}
				?>
				<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $user->ID, $post->post_author ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php
	}

	/**
	 * This saves the value
	 *
	 * @return void
	 */
	function save_feature_on_product_save() {
		// Make sure we have a product ID
		if ( isset( $_POST['ID'] ) && ! empty( $_POST['ID'] ) ) {
			$product_id = $_POST['ID'];
		} else {
			return;
		}

		if ( empty( $_POST['ibd_author_select'] ) ) {
			return;
		}

		$author_id = absint( $_POST['ibd_author_select'] );

		it_exchange_update_product_feature( $product_id, $this->slug, array(
			'author_id' => $author_id
		) );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @param integer $product_id the product id
	 * @param mixed   $values     the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function save_feature( $product_id, $values, $options = array() ) {
		$author_id = $values['author_id'];

		$data = array(
			'ID'          => $product_id,
			'post_type'   => 'it_exchange_prod',
			'post_author' => $author_id,
		);

		remove_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );

		$result = wp_update_post( $data );

		add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );

		return $result;
	}

	/**
	 * Return the product's features
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	function get_feature( $existing, $product_id, $options = array() ) {
		$product = get_post( $product_id );

		return $product->post_author;
	}

	/**
	 * Check if the product have the feature.
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_has_feature( $result, $product_id, $options = array() ) {
		if ( false === it_exchange_product_supports_feature( $product_id, $this->slug ) ) {
			return false;
		}

		return (boolean) it_exchange_get_product_feature( $product_id, $this->slug );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, $this->slug ) ) {
			return false;
		}

		return true;
	}
}