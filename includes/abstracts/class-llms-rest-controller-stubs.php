<?php
/**
 * Base REST Controller Class.
 *
 * All methods which *must* be defined by extending classes are stubbed here.
 *
 * @package  LifterLMS_REST/Abstracts
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Controller_Stubs class.
 *
 * @since [version]
 */
abstract class LLMS_REST_Controller_Stubs extends WP_REST_Controller {

	/**
	 * Base Resource
	 *
	 * For example: "courses" or "students".
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Get object.
	 *
	 * @since [version]
	 *
	 * @param int $id Object ID.
	 * @return object|WP_Error
	 */
	abstract protected function get_object( $id );

	/**
	 * Determine if the current user can view the requested item.
	 *
	 * @since [version]
	 *
	 * @param int $item_id WP_User id.
	 * @return bool
	 */
	protected function check_read_item_permissions( $item_id ) {

		// Translators: %s = method name.
		return llms_rest_server_error( sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ) );

	}

	/**
	 * Insert the prepared data into the database.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared Prepared item data.
	 * @param WP_REST_Request $request Request object.
	 * @return obj Object Instance of object from $this->get_object().
	 */
	protected function create_object( $prepared, $request ) {

		// @todo: add version to message.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::create_object', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return $this->get_object( $this->get_object_id( $prepared ) );

	}

	/**
	 * Retrieve an ID from the object
	 *
	 * @since [version]
	 *
	 * @param obj $object Item object.
	 * @return int
	 */
	protected function get_object_id( $object ) {
		if ( is_object( $object ) && ! empty( $object->id ) ) {
			return $object->id;
		} elseif ( is_array( $object ) && ! empty( $object['id'] ) ) {
			return $object['id'];
		} elseif ( method_exists( $object, 'get_id' ) ) {
			return $object->get_id();
		} elseif ( method_exists( $object, 'get' ) ) {
			return $object->get( 'id' );
		}

		// @todo: add version to message.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::get_object_id', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return 0;

	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return object
	 */
	protected function get_objects_query( $prepared, $request ) {

		// Todo: update version number.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::get_objects_query', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return new WP_Query( $prepared );

	}

	/**
	 * Retrieve an array of objects from the result of $this->get_objects_query().
	 *
	 * @since [version]
	 *
	 * @param obj $query Objects query result.
	 * @return obj[]
	 */
	protected function get_objects_from_query( $query ) {

		// Todo: update version number.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::get_objects_from_query', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return array();

	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since [version]
	 *
	 * @param obj             $query Objects query result.
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return array {
	 *     Array of pagination information.
	 *
	 *     @type int $current_page Current page number.
	 *     @type int $total_results Total number of results.
	 *     @type int $total_pages Total number of results pages.
	 * }
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		// Todo: update version number.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::get_pagination_data_from_query', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return array(
			'current_page'  => 1,
			'total_results' => 1,
			'total_pages'   => 1,
		);

	}

	/**
	 * Update the object in the database with prepared data.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared Prepared item data.
	 * @param WP_REST_Request $request Request object.
	 * @return obj Object Instance of object from $this->get_object().
	 */
	protected function update_object( $prepared, $request ) {

		// @todo: add version to message.

		// Translators: %s = method name.
		_doing_it_wrong( 'LLMS_REST_Controller::update_object', sprintf( __( "Method '%s' must be overridden.", 'lifterlms' ), __METHOD__ ), '[version]' );

		// For example.
		return $this->get_object( $prepared['id'] );

	}

}