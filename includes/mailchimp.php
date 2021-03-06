<?php

	/**
	 * MailChimp form shortcode
	 * @return string Shortcode markup
	 */
	function mailchimp_form( $atts ) {

		// Get shortcode atts
		$mailchimp = shortcode_atts( array(
			'id' => null,
			'label' => 'Subscribe',
			'placeholder' => '',
		), $atts );

		// Status
		$status = mailchimp_get_session( 'mailchimp_status', true );
		$success = mailchimp_get_session( 'mailchimp_success', true );
		$email = mailchimp_get_session( 'mailchimp_email', true );

		// Make sure ID is provided
		if ( is_null( $mailchimp['id'] ) || $mailchimp['id'] === '' ) return;

		// Get options
		$options = mailchimp_get_theme_options();
		$tarpit = empty( $options['honeypot'] ) ? '' : '<div class="mailchimp-form-row ' . esc_attr( $options['honeypot'] ) . '"><div class="mailchimp-form-grid-label"><label for="mailchimp_email_confirm">If you are human, leave this blank</label></div><div class="mailchimp-form-grid-input"><input type="text" id="mailchimp_email_confirm" name="mailchimp_email_confirm" value="" autofill="off"></div></div>';

		if ( $success ) {
			return '<p id="mailchimp-form-' . esc_attr( $mailchimp['id'] ) . '"><em>' . stripslashes( $status ) . '</em></p>';
		}

		return
			'<form class="mailchimp-form" id="mailchimp-form-' . esc_attr( $mailchimp['id'] ) . '" name="mailchimp_form" action="" method="post">' .
				'<input type="hidden" name="mailchimp_id" value="' . esc_attr( $mailchimp['id'] ) . '">' .
				'<input type="hidden" id="mailchimp_tarpit_time" name="mailchimp_tarpit_time" value="' . esc_attr( current_time( 'timestamp' ) ) . '">' .
				$tarpit .
				'<input type="hidden" id="mailchimp_submit" name="mailchimp_submit" value="' . get_site_option( 'gmt_mailchimp_submit_hash' ) . '">' .
				'<label class="mailchimp-form-label" for="mailchimp_email">' . __( 'Email Address', 'mailchimp' ) . '</label>' .
				'<div class="mailchimp-form-row">' .
					'<div class="mailchimp-form-grid-input">' .
						'<input type="email" class="mailchimp-form-input" id="mailchimp_email" name="mailchimp_email" title="The domain portion of the email address is invalid (the portion after the @)." pattern="^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*(\.\w{2,})+$" value="' . esc_attr( $email ) . '" placeholder="' . esc_attr( $mailchimp['placeholder'] ) . '" required>' .
					'</div>' .
					'<div class="mailchimp-form-grid-button">' .
						'<button class="mailchimp-form-button">' . $mailchimp['label'] . '</button>' .
					'</div>' .
				'</div>' .
				( empty( $status ) ? '' : '<p><em>' . esc_html( stripslashes( $status ) ) . '</em></p>' ) .
			'</form>';

	}
	add_shortcode( 'mailchimp', 'mailchimp_form' );



	/**
	 * Add subscriber to MailChimp
	 * @param  array $form  The submitted form data
	 */
	function mailchimp_add_new_member_to_mailchimp( $form ) {

		// Make sure email is provided
		if ( empty( $form['email'] ) || !is_array( $form['details'] ) || !array_key_exists( 'list_id', $form['details'] ) ) return;

		// Get MailChimp API variables
		$options = mailchimp_get_theme_options();

		// Create interest groups array
		if ( empty( $form['details']['interests'] ) ) {
			$interests = new stdClass();
		} else {
			$interests = array();
			foreach ( $form['details']['interests'] as $key => $group ) {
				$interests[$key] = true;
			}
		}

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $form['details']['list_id'] . '/members';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'body' => json_encode(array(
				'status' => ( array_key_exists('double_optin', $form['details']) && $form['details']['double_optin'] === 'on' ? 'pending' : 'subscribed' ),
				'email_address' => $form['email'],
				'interests' => $interests,
			)),
		);

		// Add subscriber
		$request = wp_remote_post( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If subscriber already exists, update profile
		if ( array_key_exists( 'status', $data ) && $data['status'] === 400 && $data['title'] === 'Member Exists' ) {

			$url .= '/' . md5( $form['email'] );
			$params = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
				),
				'method' => 'PUT',
				'body' => json_encode(array(
					'interests' => $interests,
				)),
			);
			$request = wp_remote_request( $url, $params );
			$response = wp_remote_retrieve_body( $request );

			// If still pending, return "new" status again
			if ( array_key_exists( 'status', $data ) && $data['status'] === 'pending' ) return 'new';

			return 'updated';

		}

		// If something went wrong, throw an error
		if ( array_key_exists( 'status', $data ) && $data['status'] === 404 ) return 'error';

		return 'new';

	}



	/**
	 * Process MailChimp form
	 */
	function mailchimp_process_form() {

		// Check that form was submitted
		if ( !isset( $_POST['mailchimp_submit'] ) ) return;

		// Verify data came from proper screen
		if ( strcmp( $_POST['mailchimp_submit'], get_site_option( 'gmt_mailchimp_submit_hash' ) ) !== 0 ) return;

		// Variables
		$details = get_post_meta( $_POST['mailchimp_id'], 'mailchimp_details', true );
		$referrer = mailchimp_get_url();
		$status = add_query_arg( 'gmt-mailchimp-form', 'submitted', $referrer . '#mailchimp-form-' . $_POST['mailchimp_id'] );

		// Make sure form has an ID
		if ( !isset( $_POST['mailchimp_id'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Sanity check
		if ( empty( $_POST['mailchimp_email'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Empty field honeypot
		if ( isset( $_POST['mailchimp_email_confirm'] ) && !empty( $_POST['mailchimp_email_confirm'] )  ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Timestamp honeypot
		if ( !isset( $_POST['mailchimp_tarpit_time'] ) || current_time( 'timestamp' ) - $_POST['mailchimp_tarpit_time'] < 1 ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// If email is invalid
		if ( empty( filter_var( $_POST['mailchimp_email'], FILTER_VALIDATE_EMAIL ) ) ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_bad_email'], 'post' );
			mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// Process signup
		$form = array(
			'email' => $_POST['mailchimp_email'],
			'details' => $details,
		);
		$signup = mailchimp_add_new_member_to_mailchimp( $form );

		// If signup failed
		if ( $signup === 'error' ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_failed'], 'post' );
			mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// If new member was added
		if ( $signup === 'new' ) {
			$alert = ( array_key_exists('double_optin', $form['details']) && $form['details']['double_optin'] === $details['alert_pending'] ? 'pending' : $details['alert_success'] );
			mailchimp_set_session( 'mailchimp_status', $alert, 'post' );
			mailchimp_set_session( 'mailchimp_success', true );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// If existing user updated
		if ( $signup === 'updated' ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_success'], 'post' );
			mailchimp_set_session( 'mailchimp_success', true );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// If sign up fails, throw error
		mailchimp_set_session( 'mailchimp_status', $details['alert_failed'], 'post' );
		mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
		wp_safe_redirect( $status, 302 );
		exit;

	}
	add_action( 'init', 'mailchimp_process_form' );