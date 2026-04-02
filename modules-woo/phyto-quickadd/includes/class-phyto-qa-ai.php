<?php
/**
 * AI description generation for Phyto Quick Add.
 *
 * @package PhytoQuickAdd
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_QA_AI {

	/**
	 * Generate a product description using the configured AI provider.
	 *
	 * @param string $name    Product name.
	 * @param string $context Additional context (tags, category, etc.).
	 * @return string|WP_Error Generated description or error.
	 */
	public static function generate( $name, $context = '' ) {
		$provider = get_option( 'phyto_qa_ai_provider', 'claude' );
		$api_key  = get_option( 'phyto_qa_ai_key_' . $provider, '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_key', __( 'No API key configured for the selected provider.', 'phyto-quickadd' ) );
		}

		$prompt = self::build_prompt( $name, $context );

		switch ( $provider ) {
			case 'claude':
				return self::call_claude( $api_key, $prompt );
			case 'openai':
				return self::call_openai( $api_key, $prompt );
			case 'gemini':
				return self::call_gemini( $api_key, $prompt );
			case 'mistral':
				return self::call_mistral( $api_key, $prompt );
			case 'cohere':
				return self::call_cohere( $api_key, $prompt );
			default:
				return new WP_Error( 'unknown_provider', __( 'Unknown AI provider.', 'phyto-quickadd' ) );
		}
	}

	private static function build_prompt( $name, $context ) {
		$prompt = "Write a compelling WooCommerce product description for the following plant:\n\nName: {$name}";
		if ( $context ) {
			$prompt .= "\nContext: {$context}";
		}
		$prompt .= "\n\nWrite 2-3 short paragraphs covering: growing conditions, collector appeal, and care tips. Use plain text, no markdown. Keep it under 150 words.";
		return $prompt;
	}

	private static function call_claude( $key, $prompt ) {
		$response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
			'timeout' => 30,
			'headers' => array(
				'x-api-key'         => $key,
				'anthropic-version' => '2023-06-01',
				'content-type'      => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'      => 'claude-haiku-4-5-20251001',
				'max_tokens' => 300,
				'messages'   => array( array( 'role' => 'user', 'content' => $prompt ) ),
			) ),
		) );
		return self::extract_response( $response, 'claude' );
	}

	private static function call_openai( $key, $prompt ) {
		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'    => 'gpt-4o-mini',
				'messages' => array( array( 'role' => 'user', 'content' => $prompt ) ),
			) ),
		) );
		return self::extract_response( $response, 'openai' );
	}

	private static function call_gemini( $key, $prompt ) {
		$url      = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . rawurlencode( $key );
		$response = wp_remote_post( $url, array(
			'timeout' => 30,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array(
				'contents' => array( array( 'parts' => array( array( 'text' => $prompt ) ) ) ),
			) ),
		) );
		return self::extract_response( $response, 'gemini' );
	}

	private static function call_mistral( $key, $prompt ) {
		$response = wp_remote_post( 'https://api.mistral.ai/v1/chat/completions', array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'    => 'mistral-small-latest',
				'messages' => array( array( 'role' => 'user', 'content' => $prompt ) ),
			) ),
		) );
		return self::extract_response( $response, 'mistral' );
	}

	private static function call_cohere( $key, $prompt ) {
		$response = wp_remote_post( 'https://api.cohere.com/v1/generate', array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode( array(
				'model'      => 'command-r',
				'prompt'     => $prompt,
				'max_tokens' => 300,
			) ),
		) );
		return self::extract_response( $response, 'cohere' );
	}

	private static function extract_response( $response, $provider ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 || ! $body ) {
			$msg = isset( $body['error']['message'] ) ? $body['error']['message'] : "HTTP {$code}";
			return new WP_Error( 'api_error', $msg );
		}

		switch ( $provider ) {
			case 'claude':
				return isset( $body['content'][0]['text'] ) ? trim( $body['content'][0]['text'] ) : new WP_Error( 'parse', 'No content' );
			case 'openai':
			case 'mistral':
				return isset( $body['choices'][0]['message']['content'] ) ? trim( $body['choices'][0]['message']['content'] ) : new WP_Error( 'parse', 'No content' );
			case 'gemini':
				return isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ? trim( $body['candidates'][0]['content']['parts'][0]['text'] ) : new WP_Error( 'parse', 'No content' );
			case 'cohere':
				return isset( $body['generations'][0]['text'] ) ? trim( $body['generations'][0]['text'] ) : new WP_Error( 'parse', 'No content' );
		}
		return new WP_Error( 'parse', 'Unknown provider' );
	}

	/**
	 * Quick connectivity test — returns true or error string.
	 */
	public static function test( $provider, $key ) {
		$original_provider = get_option( 'phyto_qa_ai_provider' );
		$original_key      = get_option( 'phyto_qa_ai_key_' . $provider );

		update_option( 'phyto_qa_ai_provider', $provider );
		update_option( 'phyto_qa_ai_key_' . $provider, $key );

		$result = self::generate( 'Echeveria elegans', 'succulent, rosette' );

		update_option( 'phyto_qa_ai_provider', $original_provider );
		update_option( 'phyto_qa_ai_key_' . $provider, $original_key );

		return is_wp_error( $result ) ? $result->get_error_message() : true;
	}
}
