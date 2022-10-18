<?php
/**
 * Не используется
 *
 */


apply_filters('woocommerce_customer_meta_fields', 'change_customer_fields');
function change_customer_fields($array){

	$array = array(
		'billing'  => array(
			'title'  => __( 'Customer billing address', 'woocommerce' ),
			'fields' => array(
				'billing_first_name' => array(
					'label'       => __( 'First name', 'woocommerce' ),
					'description' => '',
				),
				'billing_last_name'  => array(
					'label'       => __( 'Last name', 'woocommerce' ),
					'description' => '',
				),
				'billing_company'    => array(
					'label'       => __( 'Company', 'woocommerce' ),
					'description' => '',
				),
				'billing_address_1'  => array(
					'label'       => __( 'Address line 1', 'woocommerce' ),
					'description' => '',
				),
		/*		'billing_address_2'  => array(
					'label'       => __( 'Address line 2', 'woocommerce' ),
					'description' => '',
				),*/
				'billing_city'       => array(
					'label'       => __( 'City', 'woocommerce' ),
					'description' => '',
				),
/*				'billing_postcode'   => array(
					'label'       => __( 'Postcode / ZIP', 'woocommerce' ),
					'description' => '',
				),*/
				'billing_country'    => array(
					'label'       => __( 'Country', 'woocommerce' ),
					'description' => '',
					'class'       => 'js_field-country',
					'type'        => 'select',
					'options'     => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_allowed_countries(),
				),
/*				'billing_state'      => array(
					'label'       => __( 'State / County', 'woocommerce' ),
					'description' => __( 'State / County or state code', 'woocommerce' ),
					'class'       => 'js_field-state',
				),*/
				'billing_phone'      => array(
					'label'       => __( 'Phone', 'woocommerce' ),
					'description' => '',
				),
				'billing_email'      => array(
					'label'       => __( 'Email address', 'woocommerce' ),
					'description' => '',
				),
			),
		),

		/*'shipping' => array(
			'title'  => __( 'Customer shipping address', 'woocommerce' ),
			'fields' => array(
				'copy_billing'        => array(
					'label'       => __( 'Copy from billing address', 'woocommerce' ),
					'description' => '',
					'class'       => 'js_copy-billing',
					'type'        => 'button',
					'text'        => __( 'Copy', 'woocommerce' ),
				),
				'shipping_first_name' => array(
					'label'       => __( 'First name', 'woocommerce' ),
					'description' => '',
				),
				'shipping_last_name'  => array(
					'label'       => __( 'Last name', 'woocommerce' ),
					'description' => '',
				),
				'shipping_company'    => array(
					'label'       => __( 'Company', 'woocommerce' ),
					'description' => '',
				),
				'shipping_address_1'  => array(
					'label'       => __( 'Address line 1', 'woocommerce' ),
					'description' => '',
				),
				'shipping_address_2'  => array(
					'label'       => __( 'Address line 2', 'woocommerce' ),
					'description' => '',
				),
				'shipping_city'       => array(
					'label'       => __( 'City', 'woocommerce' ),
					'description' => '',
				),
				'shipping_postcode'   => array(
					'label'       => __( 'Postcode / ZIP', 'woocommerce' ),
					'description' => '',
				),
				'shipping_country'    => array(
					'label'       => __( 'Country', 'woocommerce' ),
					'description' => '',
					'class'       => 'js_field-country',
					'type'        => 'select',
					'options'     => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_allowed_countries(),
				),
				'shipping_state'      => array(
					'label'       => __( 'State / County', 'woocommerce' ),
					'description' => __( 'State / County or state code', 'woocommerce' ),
					'class'       => 'js_field-state',
				),
			),
		),*/
	);

	unset( $array['shipping'] );

	return $array;
}