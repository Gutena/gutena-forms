<?php
/**
 * Gutena Forms Abilities Registration
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

class Gutena_Forms_Abilities {
	
	public static function init() {
		add_action( 'wp_abilities_api_categories_init', array( self::class, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( self::class, 'register_abilities' ) );
	}

	public static function register_categories() {
		wp_register_ability_category( 'gutena-forms', array(
			'label'       => __( 'Gutena Forms', 'gutena-forms' ),
			'description' => __( 'Abilities related to Gutena Forms.', 'gutena-forms' ),
		) );
	}
	
	public static function register_abilities() {
		// Ability to list all forms
		wp_register_ability( 'gutena-forms/get-forms', array(
			'label'               => __( 'Get Gutena Forms', 'gutena-forms' ),
			'description'         => __( 'Retrieve a list of all available Gutena Forms.', 'gutena-forms' ),
			'category'            => 'gutena-forms',
			'execute_callback'    => array( self::class, 'get_forms' ),
			'permission_callback' => array( self::class, 'check_permissions' ),
			'output_schema'       => array(
				'type' => 'array',
				'items' => array(
					'type' => 'object',
					'properties' => array(
						'id'    => array( 'type' => 'integer' ),
						'title' => array( 'type' => 'string' ),
					)
				)
			)
		) );
		
		// Ability to get entries and count
		wp_register_ability( 'gutena-forms/get-form-entries', array(
			'label'               => __( 'Get Form Entries', 'gutena-forms' ),
			'description'         => __( 'Retrieve submissions for a specific form including total count.', 'gutena-forms' ),
			'category'            => 'gutena-forms',
			'execute_callback'    => array( self::class, 'get_form_entries' ),
			'permission_callback' => array( self::class, 'check_permissions' ),
			'input_schema'        => array(
				'type' => 'object',
				'properties' => array(
					'form_id' => array( 'type' => 'integer', 'description' => 'The ID of the form.' ),
					'limit'   => array( 'type' => 'integer', 'default' => 50 ),
				),
				'required' => array( 'form_id' )
			)
		) );
		
		// Ability to analyze entries (duplicates, same entries)
		wp_register_ability( 'gutena-forms/get-entry-analysis', array(
			'label'               => __( 'Analyze Form Entries', 'gutena-forms' ),
			'description'         => __( 'Count total entries and identify duplicate submissions for a specific form.', 'gutena-forms' ),
			'category'            => 'gutena-forms',
			'execute_callback'    => array( self::class, 'get_entry_analysis' ),
			'permission_callback' => array( self::class, 'check_permissions' ),
			'input_schema'        => array(
				'type' => 'object',
				'properties' => array(
					'form_id' => array( 'type' => 'integer', 'description' => 'The ID of the form.' ),
				),
				'required' => array( 'form_id' )
			)
		) );
		
		// Ability to delete (trash) an entry
		wp_register_ability( 'gutena-forms/delete-entry', array(
			'label'               => __( 'Delete Form Entry', 'gutena-forms' ),
			'description'         => __( 'Safely trashes a specific form entry by its ID.', 'gutena-forms' ),
			'category'            => 'gutena-forms',
			'execute_callback'    => array( self::class, 'delete_entry' ),
			'permission_callback' => array( self::class, 'check_permissions' ),
			'input_schema'        => array(
				'type' => 'object',
				'properties' => array(
					'entry_id' => array( 'type' => 'integer', 'description' => 'The ID of the entry to delete.' ),
				),
				'required' => array( 'entry_id' )
			)
		) );
	}
	
	public static function check_permissions() {
		return current_user_can( 'manage_options' );
	}
	
	public static function get_forms() {
		global $wpdb;
		$table = $wpdb->prefix . 'gutenaforms';
		$results = $wpdb->get_results( "SELECT form_id as id, form_name as title FROM $table WHERE published = 1" );
		return $results ?: array();
	}
	
	public static function get_form_entries( $input ) {
		global $wpdb;
		$form_id = (int) $input['form_id'];
		$limit   = (int) ( $input['limit'] ?? 50 );
		
		$table_entries = $wpdb->prefix . 'gutenaforms_entries';
		
		$total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_entries WHERE form_id = %d AND trash = 0", $form_id ) );
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT entry_id, entry_data, added_time FROM $table_entries WHERE form_id = %d AND trash = 0 ORDER BY added_time DESC LIMIT %d", $form_id, $limit ) );
		
		foreach ( $entries as &$entry ) {
			$entry->entry_data = maybe_unserialize( $entry->entry_data );
		}
		
		return array(
			'total_count' => (int) $total_count,
			'entries'     => $entries
		);
	}
	
	public static function get_entry_analysis( $input ) {
		global $wpdb;
		$form_id = (int) $input['form_id'];
		$table_entries = $wpdb->prefix . 'gutenaforms_entries';
		
		// Get all entries for this form to analyze duplicates
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT entry_data FROM $table_entries WHERE form_id = %d AND trash = 0", $form_id ) );
		
		$total_count = count( $entries );
		$counts = array();
		$duplicates = array();
		
		foreach ( $entries as $entry ) {
			// Unserialize and normalize for comparison
			$data = maybe_unserialize( $entry->entry_data );
			$hash = md5( json_encode( $data ) );
			
			if ( ! isset( $counts[ $hash ] ) ) {
				$counts[ $hash ] = array(
					'count' => 0,
					'data'  => $data
				);
			}
			$counts[ $hash ]['count']++;
		}
		
		foreach ( $counts as $hash => $info ) {
			if ( $info['count'] > 1 ) {
				$duplicates[] = array(
					'duplicate_count' => $info['count'],
					'entry_data'      => $info['data']
				);
			}
		}
		
		return array(
			'form_id'         => $form_id,
			'total_entries'   => $total_count,
			'unique_entries'  => count( $counts ),
			'duplicate_groups' => count( $duplicates ),
			'duplicates'      => $duplicates
		);
	}
	
	public static function delete_entry( $input ) {
		global $wpdb;
		$entry_id = (int) $input['entry_id'];
		$table = $wpdb->prefix . 'gutenaforms_entries';
		
		// Check if entry exists and is not already trashed
		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT entry_id FROM $table WHERE entry_id = %d AND trash = 0", $entry_id ) );
		
		if ( ! $entry ) {
			return array(
				'success' => false,
				'message' => __( 'Entry not found or already deleted.', 'gutena-forms' )
			);
		}
		
		// Trash the entry
		$result = $wpdb->update(
			$table,
			array( 'trash' => 1 ),
			array( 'entry_id' => $entry_id ),
			array( '%d' ),
			array( '%d' )
		);
		
		return array(
			'success' => $result !== false,
			'message' => $result !== false ? sprintf( __( 'Entry %d has been trashed.', 'gutena-forms' ), $entry_id ) : __( 'Failed to trash entry.', 'gutena-forms' )
		);
	}
}

Gutena_Forms_Abilities::init();
