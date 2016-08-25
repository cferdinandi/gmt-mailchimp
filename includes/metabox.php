<?php

	/**
	 * Create the metabox
	 */
	function mailchimp_create_metabox() {
		add_meta_box( 'mailchimp_metabox', 'Form Details', 'mailchimp_render_metabox', 'gmt-mailchimp', 'normal', 'default');
	}
	add_action( 'add_meta_boxes', 'mailchimp_create_metabox' );



	/**
	 * Create the metabox default values
	 */
	function mailchimp_metabox_defaults() {
		$options = mailchimp_get_theme_options();
		return array(

			// MailChimp Info
			'list_id' => $options['mailchimp_list_id'],
			'category' => '',
			'group' => '',

			// Alerts
			'alert_bad_email' => 'Please use a valid email address.',
			'alert_failed' => 'Well this is embarrassing... something went wrong. Please try again.',
			'alert_pending' => 'Almost finished... We just need to confirm your email address. To complete the subscription process, please click the link in the email we just sent you.',
			'alert_success' => 'Congrats! You\'re subscribed.',

		);
	}



	/**
	 * Get data from the MailChimp API
	 * @param  string $group The group ID
	 * @return array         Data from the MailChimp API
	 */
	function mailchimp_metabox_get_mailchimp_data( $list_id, $group = null ) {

		$options = mailchimp_get_theme_options();

		if ( empty( $options['mailchimp_api_key'] ) || empty( $list_id ) ) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $list_id . '/interest-categories' . ( empty( $group ) ? '' : '/' . $group . '/interests' );
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Get data from  MailChimp
		$request = wp_remote_get( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If request fails, bail
		if ( empty( $group ) ) {
			if ( !array_key_exists( 'categories', $data ) || !is_array( $data['categories'] ) || empty( $data['categories'] ) ) return array();
		} else {
			if ( !array_key_exists( 'interests', $data ) || !is_array( $data['interests'] ) || empty( $data['interests'] ) ) return array();
		}

		return $data;

	}



	/**
	 * Create MailChimp category select
	 * @param  array $details  Saved data
	 */
	function mailchimp_metabox_field_category_id( $details ) {
		$mailchimp = mailchimp_metabox_get_mailchimp_data( $details['list_id'] );
		?>
		<div>
			<label for="mailchimp_category"><?php _e( 'Category', 'mailchimp' ); ?></label>
			<select id="mailchimp_category" name="mailchimp[category]">
				<option value="" <?php selected( '', $details['category'] ) ?>><?php _e( 'None', 'mailchimp' ); ?></option>
				<?php foreach ( $mailchimp['categories'] as $key => $category ) : ?>
					<option value="<?php echo esc_attr( $category['id'] ); ?>" <?php selected( $category['id'], $details['category'] ); ?>><?php echo esc_html( $category['title'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<br>
		<?php
	}



	/**
	 * Create MailChimp group select
	 * @param  array $details  Saved data
	 */
	function mailchimp_metabox_field_group_id( $details ) {
		$mailchimp = mailchimp_metabox_get_mailchimp_data( $details['list_id'], $details['category'] );
		?>
		<div>
			<label for="mailchimp_group"><?php _e( 'Group', 'mailchimp' ); ?></label>
			<select id="mailchimp_group" name="mailchimp[group]">
				<option value="" <?php selected( '', $details['group'] ) ?>><?php _e( 'None', 'mailchimp' ); ?></option>
				<?php foreach ( $mailchimp['interests'] as $key => $interest ) : ?>
					<option value="<?php echo esc_attr( $interest['id'] ); ?>" <?php selected( $interest['id'], $details['group'] ); ?>><?php echo esc_html( $interest['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<br>
		<?php
	}



	/**
	 * Render the metabox
	 */
	function mailchimp_render_metabox() {

		// Variables
		global $post;
		$saved = get_post_meta( $post->ID, 'mailchimp_details', true );
		$defaults = mailchimp_metabox_defaults();
		$details = wp_parse_args( $saved, $defaults );

		?>

			<fieldset>

				<div>
					<label for="mailchimp_shortcode"><?php _e( 'Shortcode', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_shortcode" name="mailchimp_shortcode" value="<?php echo esc_attr( '[mailchimp id="' . $post->ID . '" label="Subscribe" placeholder=""]' ); ?>" readonly="readonly">
				</div>
				<br>

				<div>
					<label for="mailchimp_list_id"><?php _e( 'List ID', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_list_id" name="mailchimp[list_id]" value="<?php echo esc_attr( $details['list_id'] ); ?>">
				</div>
				<br>

				<?php mailchimp_metabox_field_category_id( $details ); ?>

				<?php mailchimp_metabox_field_group_id( $details ); ?>

				<div>
					<label for="mailchimp_alert_bad_email"><?php _e( 'Alert: Bad Email', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_alert_bad_email" name="mailchimp[alert_bad_email]" value="<?php echo esc_attr( $details['alert_bad_email'] ); ?>">
				</div>
				<br>

				<div>
					<label for="mailchimp_alert_failed"><?php _e( 'Alert: Failed', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_alert_failed" name="mailchimp[alert_failed]" value="<?php echo esc_attr( $details['alert_failed'] ); ?>">
				</div>
				<br>

				<div>
					<label for="mailchimp_alert_pending"><?php _e( 'Alert: Pending', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_alert_pending" name="mailchimp[alert_pending]" value="<?php echo esc_attr( $details['alert_pending'] ); ?>">
				</div>
				<br>

				<div>
					<label for="mailchimp_alert_success"><?php _e( 'Alert: Success', 'mailchimp' ); ?></label>
					<input type="text" class="large-text" id="mailchimp_alert_success" name="mailchimp[alert_success]" value="<?php echo esc_attr( $details['alert_success'] ); ?>">
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'mailchimp_form_metabox_nonce', 'mailchimp_form_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function mailchimp_save_metabox( $post_id, $post ) {

		if ( !isset( $_POST['mailchimp_form_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['mailchimp_form_metabox_process'], 'mailchimp_form_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Check that events details are being passed along
		if ( !isset( $_POST['mailchimp'] ) ) {
			return $post->ID;
		}

		// Sanitize all data
		$sanitized = array();
		foreach ( $_POST['mailchimp'] as $key => $detail ) {
			// if ( in_array( $key, array( 'date_start', 'date_end' ) ) ) {
			// 	$sanitized[$key] = strtotime( $detail );
			// 	continue;
			// }
			$sanitized[$key] = wp_filter_post_kses( $detail );
		}

		// Update data in database
		update_post_meta( $post->ID, 'mailchimp_details', $sanitized );

	}
	add_action('save_post', 'mailchimp_save_metabox', 1, 2);



	/**
	 * Save events data to revisions
	 * @param  Number $post_id The post ID
	 */
	function mailchimp_save_revisions( $post_id ) {

		// Check if it's a revision
		$parent_id = wp_is_post_revision( $post_id );

		// If is revision
		if ( $parent_id ) {

			// Get the data
			$parent = get_post( $parent_id );
			$details = get_post_meta( $parent->ID, 'mailchimp_details', true );

			// If data exists, add to revision
			if ( !empty( $details ) && is_array( $details ) ) {
				$defaults = mailchimp_metabox_defaults();
				foreach ( $defaults as $key => $value ) {
					if ( array_key_exists( $key, $details ) ) {
						add_metadata( 'post', $post_id, 'mailchimp_details_' . $key, $details[$key] );
					}
				}
			}

		}

	}
	add_action( 'save_post', 'mailchimp_save_revisions' );



	/**
	 * Restore events data with post revisions
	 * @param  Number $post_id     The post ID
	 * @param  Number $revision_id The revision ID
	 */
	function mailchimp_restore_revisions( $post_id, $revision_id ) {

		// Variables
		$post = get_post( $post_id );
		$revision = get_post( $revision_id );
		$defaults = mailchimp_metabox_defaults();
		$details = array();

		// Update content
		foreach ( $defaults as $key => $value ) {
			$detail_revision = get_metadata( 'post', $revision->ID, 'mailchimp_details_' . $key, true );
			if ( isset( $detail_revision ) ) {
				$details[$key] = $detail_revision;
			}
		}
		update_post_meta( $post_id, 'mailchimp_details', $event_details );

	}
	add_action( 'wp_restore_post_revision', 'mailchimp_restore_revisions', 10, 2 );



	/**
	 * Get the data to display on the revisions page
	 * @param  Array $fields The fields
	 * @return Array The fields
	 */
	function mailchimp_get_revisions_fields( $fields ) {
		$defaults = mailchimp_metabox_defaults();
		foreach ( $defaults as $key => $value ) {
			$fields['mailchimp_details_' . $key] = ucfirst( $key );
		}
		return $fields;
	}
	add_filter( '_wp_post_revision_fields', 'mailchimp_get_revisions_fields' );



	/**
	 * Display the data on the revisions page
	 * @param  String|Array $value The field value
	 * @param  Array        $field The field
	 */
	function mailchimp_display_revisions_fields( $value, $field ) {
		global $revision;
		return get_metadata( 'post', $revision->ID, $field, true );
	}
	add_filter( '_wp_post_revision_field_my_meta', 'mailchimp_display_revisions_fields', 10, 2 );