<?php


	/**
	 * Add members to MailChimp when they sign up
	 * @param  string $username The new member's username
	 * @param  string $email    The new member's email address
	 */
	function beacon_add_new_member_to_mailchimp( $username = null, $email = null ) {

		// Make sure username and email are provided
		if ( empty( $username ) || empty( $email ) ) return;

		// Get MailChimp API variables
		$options = beacon_get_theme_options();

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'body' => json_encode(array(
				'status' => 'subscribed',
				'merge_fields' => array(
					'FNAME' => $username,
				),
				'email_address' => $email,
				'interests' => array(
					$options['mailchimp_group_id'] => true,
				),
			)),
		);

		// Add subscriber
		$request = wp_remote_post( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If subscriber already exists, update profile
		if ( $data['status'] === 400 && $data['title'] === 'Member Exists' ) {

			$url .= '/' . md5( $email );
			$params = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
				),
				'method' => 'PUT',
				'body' => json_encode(array(
					'merge_fields' => array(
						'FNAME' => $username,
					),
					'interests' => array(
						$options['mailchimp_group_id'] => true,
					),
				)),
			);
			$request = wp_remote_post( $url, $params );
			$response = wp_remote_retrieve_body( $request );
			$data = json_decode( $response, true );

		}

	}
	add_action( 'wpwebapp_after_signup', 'beacon_add_new_member_to_mailchimp', 10, 2 );