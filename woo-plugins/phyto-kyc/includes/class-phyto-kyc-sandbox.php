<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Thin wrapper around sandbox.co.in identity verification API.
 * Handles token acquisition and PAN/GST verification calls.
 */
class Phyto_KYC_Sandbox {

    private static function get_token(): string {
        $cached  = get_transient( 'phyto_kyc_sandbox_token' );
        if ( $cached ) return $cached;

        $settings = get_option( 'phyto_kyc_settings', [] );
        $api_key  = $settings['api_key'] ?? '';
        if ( ! $api_key ) return '';

        $response = wp_remote_post( 'https://api.sandbox.co.in/authenticate', [
            'headers' => [ 'x-api-key' => $api_key, 'Content-Type' => 'application/json' ],
            'body'    => '{}',
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) return '';
        $body  = json_decode( wp_remote_retrieve_body( $response ), true );
        $token = $body['access_token'] ?? '';
        if ( $token ) set_transient( 'phyto_kyc_sandbox_token', $token, 3600 );
        return $token;
    }

    public static function verify_pan( string $pan ): array {
        $token = self::get_token();
        if ( ! $token ) return [ 'success' => false, 'error' => 'No API token' ];

        $settings = get_option( 'phyto_kyc_settings', [] );
        $response = wp_remote_post( 'https://api.sandbox.co.in/kyc/pan/verify', [
            'headers' => [
                'Authorization' => $token,
                'x-api-key'     => $settings['api_key'] ?? '',
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [ '@entity' => 'in.co.sandbox.kyc.pan.verify.request', 'pan' => strtoupper( $pan ) ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) return [ 'success' => false, 'error' => $response->get_error_message() ];
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return [
            'success' => ( $body['data']['status'] ?? '' ) === 'VALID',
            'name'    => $body['data']['name'] ?? '',
            'raw'     => $body,
        ];
    }

    public static function verify_gst( string $gstin ): array {
        $token = self::get_token();
        if ( ! $token ) return [ 'success' => false, 'error' => 'No API token' ];

        $settings = get_option( 'phyto_kyc_settings', [] );
        $response = wp_remote_get( 'https://api.sandbox.co.in/gst/taxpayer/' . strtoupper( $gstin ), [
            'headers' => [
                'Authorization' => $token,
                'x-api-key'     => $settings['api_key'] ?? '',
            ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) return [ 'success' => false, 'error' => $response->get_error_message() ];
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        return [
            'success'       => ( $body['data']['sts'] ?? '' ) === 'Active',
            'business_name' => $body['data']['tradeNam'] ?? '',
            'raw'           => $body,
        ];
    }
}
