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
		), $atts );

		// Prevent this content from caching
		define('DONOTCACHEPAGE', TRUE);

		// Status
		$status = mailchimp_get_session( 'mailchimp_status', true );
		$success = mailchimp_get_session( 'mailchimp_success', true );
		$name = mailchimp_get_session( 'mailchimp_fname', true );
		$email = mailchimp_get_session( 'mailchimp_email', true );

		// Make sure ID is provided
		if ( is_null( $mailchimp['id'] ) || $mailchimp['id'] === '' ) return;

		// Get options
		$options = mailchimp_get_theme_options();
		$tarpit = empty( $options['honeypot'] ) ? '' : '<div class="row ' . $options['honeypot'] . '"><div class="grid-third"><label for="mailchimp_email_confirm">If you are human, leave this blank</label></div><div class="grid-two-thirds"><input type="text" id="mailchimp_email_confirm" name="mailchimp_email_confirm" value="" autofill="off"></div></div>';

		if ( $success ) {
			return '<p id="mailchimp-form-' . $mailchimp['id'] . '"><em>' . stripslashes( $status ) . '</em></p>';
		}

		return
			'<form class="mailchimp-form" id="mailchimp-form-' . $mailchimp['id'] . '" name="mailchimp_form" action="" method="post">' .
				'<div class="row">' .
					'<div class="grid-third">' .
						'<label for="mailchimp_fname">' . __( 'First Name', 'mailchimp' ) . '</label>' .
					'</div>' .
					'<div class="grid-two-thirds">' .
						'<input type="text" id="mailchimp_fname" name="mailchimp_fname" value="' . $name . '">' .
					'</div>' .
				'</div>' .
				'<div class="row">' .
					'<div class="grid-third">' .
						'<label for="mailchimp_email">' . __( 'Email Address', 'mailchimp' ) . '</label>' .
					'</div>' .
					'<div class="grid-two-thirds">' .
						'<input type="email" id="mailchimp_email" name="mailchimp_email" value="' . $email . '" required>' .
					'</div>' .
				'</div>' .
				$tarpit .
				'<div class="row">' .
					'<div class="grid-two-thirds offset-third">' .
						( empty( $status ) ? '' : '<p><em>' . esc_html( stripslashes( $status ) ) . '</em></p>' ) .
					'</div>' .
				'</div>' .
				'<div class="row">' .
					'<div class="grid-two-thirds offset-third">' .
						'<input type="hidden" name="mailchimp_id" value="' . $mailchimp['id'] . '">' .
						'<input type="hidden" id="mailchimp_tarpit_time" name="mailchimp_tarpit_time" value="' . current_time( 'timestamp' ) . '">' .
						wp_nonce_field( 'mailchimp_form_nonce', 'mailchimp_form_process', true, false ) .
						'<button class="btn">' . $mailchimp['label'] . '</button>' .
					'</div>' .
				'</div>' .
			'</form>';

	}
	add_shortcode( 'mailchimp', 'mailchimp_form' );



	/**
	 * Add subscriber to MailChimp
	 * @param  array $form  The submitted form data
	 */
	function mailchimp_add_new_member_to_mailchimp( $form ) {

		// Make sure username and email are provided
		if ( empty( $form['fname'] ) && empty( $form['email'] ) ) return;

		// Get MailChimp API variables
		$options = mailchimp_get_theme_options();

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'body' => json_encode(array(
				'status' => 'pending',
				'merge_fields' => array(
					'FNAME' => $form['fname'],
				),
				'email_address' => $form['email'],
				'interests' => ( !array_key_exists( 'group', $form['details'] ) || empty( $form['details']['group'] ) ? '' : array( $form['details']['group'] => true ) ),
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
					'merge_fields' => array(
						'FNAME' => $form['fname'],
					),
					'interests' => ( !array_key_exists( 'group', $form['details'] ) || empty( $form['details']['group'] ) ? '' : array( $form['details']['group'] => true ) ),
				)),
			);
			$request = wp_remote_post( $url, $params );
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
		if ( !isset( $_POST['mailchimp_form_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['mailchimp_form_process'], 'mailchimp_form_nonce' ) ) {
			die( 'Security check' );
		}

		// // Variables
		$details = get_post_meta( $_POST['mailchimp_id'], 'mailchimp_details', true );
		$referrer = mailchimp_get_url();
		$status = $referrer . '#mailchimp-form-' . $_POST['mailchimp_id'];

		// Make sure form has an ID
		if ( !isset( $_POST['mailchimp_id'] ) ) {
			wp_safe_redirect( $referrer, 302 );
			exit;
		}

		// Sanity check
		if ( empty( $_POST['mailchimp_fname'] ) && empty( $_POST['mailchimp_email'] ) ) {
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
		if ( !is_email( $_POST['mailchimp_email'] ) ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_bad_email'], 'post' );
			mailchimp_set_session( 'mailchimp_fname', $_POST['mailchimp_fname'], 'post' );
			mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// Process signup
		$form = array(
			'fname' => $_POST['mailchimp_fname'],
			'email' => $_POST['mailchimp_email'],
			'details' => $details,
		);
		$signup = mailchimp_add_new_member_to_mailchimp( $form );

		// If signup failed
		if ( $signup === 'error' ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_failed'], 'post' );
			mailchimp_set_session( 'mailchimp_fname', $_POST['mailchimp_fname'], 'post' );
			mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
			wp_safe_redirect( $status, 302 );
			exit;
		}

		// If new member was added
		if ( $signup === 'new' ) {
			mailchimp_set_session( 'mailchimp_status', $details['alert_pending'], 'post' );
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
		mailchimp_set_session( 'mailchimp_status', $details['alert_bad_email'], 'post' );
		mailchimp_set_session( 'mailchimp_fname', $_POST['mailchimp_fname'], 'post' );
		mailchimp_set_session( 'mailchimp_email', $_POST['mailchimp_email'], 'post' );
		wp_safe_redirect( $status, 302 );
		exit;

	}
	add_action( 'init', 'mailchimp_process_form' );