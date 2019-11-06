<?php
/**
 * REST Quizzes Controller.
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Quizzes_Controller class.
 *
 * @since [version]
 */
class LLMS_REST_Quizzes_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quizzes';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_quiz';

	/**
	 * Get the Course's schema, conforming to JSON Schema.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = parent::get_item_schema();

		$quiz_properties = array(
			'attempt_limiting'    => array(
				'description' => __( 'Determine if attempt limiting is enabled. When enabled, students are locked out after the number of attempts specified by `allowed_attempts`.', 'lifterlms' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'default'     => false,
			),
			'attempts_allowed'    => array(
				'description' => __( 'Limit the number of times a student can attempt the quiz before the quiz is locked. Only used when attempt_limiting is `true`.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'course_id'           => array(
				'description' => __( 'WordPress post ID of the quizzes\'s parent course.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
				'readonly'    => true,
			),
			'parent_id'           => array(
				'description' => __( 'WordPress post ID of the parent item. Must be a Lesson ID. `0` indicates an "orphaned" quiz which can be edited and viewed by instructors and admins but cannot be taken by students.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'passing_percentage'  => array(
				'description' => __( 'Determines the grade required to consider an attempt "passing".', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
				'default'     => 65,
			),
			'randomize_questions' => array(
				'description' => __( 'When enabled, questions will be shuffled into a random order for each new quiz attempt.', 'lifterlms' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'default'     => false,
			),
			'show_correct_answer' => array(
				'description' => __( 'When enabled, students will be shown the correct answers to questions they answered incorrectly during quiz reviews.', 'lifterlms' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'default'     => false,
			),
			'time_limit'          => array(
				'description' => __( 'Determines the number of minutes allowed for each quiz attempt. Only used when `time_limiting` is `true`.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'time_limiting'       => array(
				'description' => __( 'Determine if a time limit is enforced for each attempt.When enabled, a quiz attempt is automatically ended after the time period specified by `time_limit` has passed.', 'lifterlms' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'default'     => false,
			),
			'total_points'        => array(
				'description' => __( 'The total points of all questions within the quiz.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
		);

		/**
		 * Unset parent properties not used in quizzes.
		 */
		unset(
			$schema['properties']['comment_status'],
			$schema['properties']['excerpt'],
			$schema['properties']['featured_media'],
			$schema['properties']['menu_order'],
			$schema['properties']['password'],
			$schema['properties']['ping_status']
		);

		/**
		 * Only allow 'publish' and 'draft' statuses
		 */
		$quiz_properties['status']['enum'] = array(
			'publish',
			'draft',
		);

		$schema['properties'] = array_merge( $schema['properties'], $quiz_properties );

		/**
		 * Filter item schema for the quizzes controller.
		 *
		 * @since [version]
		 *
		 * @param array $schema Item schema data.
		 */
		return apply_filters( 'llms_rest_quiz_item_schema', $schema );

	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since [version]
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['parent'] = array(
			'description'       => __( 'Filter quizzes by the parent post (lesson) ID.', 'lifterlms' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
		);

		$query_params['course'] = array(
			'description'       => __( 'Filter quizzes by the parent course ID.', 'lifterlms' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
		);

		$query_params['status'] = array(
			'description' => __( 'Filter quizzes by the post status.', 'lifterlms' ),
			'type'        => 'string',
			'enum'        => array(
				'publish',
				'draft',
			),
		);

		return $query_params;

	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz       $quiz    Quiz object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $quiz, $request ) {

		$data = parent::prepare_object_for_response( $quiz, $request );

		$data['attempt_limiting'] = $quiz->has_attempt_limit();

		$data['attempts_allowed'] = $quiz->get( 'allowed_attempts' );

		$course            = $quiz->get_course();
		$data['course_id'] = $course ? $course->get( 'id' ) : 0;

		$data['parent_id'] = $quiz->get( 'lesson_id' );

		$data['passing_percentage'] = $quiz->get( 'passing_percent' );

		$data['randomize_questions'] = llms_parse_bool( $quiz->get( 'random_questions' ) );

		$data['show_correct_answer'] = llms_parse_bool( $quiz->get( 'show_correct_answer' ) );

		$data['time_limit'] = $quiz->get( 'time_limit' );

		$data['time_limiting'] = $quiz->has_time_limit();

		// consider moving this query in core LLMS_Quiz.
		global $wpdb;
		$data['total_points'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(pm.meta_value)
				FROM {$wpdb->postmeta} AS pm JOIN {$wpdb->postmeta} AS pm2
				WHERE pm.meta_key = '_llms_points'
				AND pm.post_id = pm2.post_id
				AND pm2.meta_key = '_llms_parent_id'
				AND pm2.meta_value = %d
				",
				$quiz->get( 'id' )
			)
		);

		/**
		 * Filters the quiz data for a response.
		 *
		 * @since [version]
		 *
		 * @param array           $data    Array of quiz properties prepared for response.
		 * @param LLMS_Quiz       $quiz    Quiz object.
		 * @param WP_REST_Request $request Full details about the request.
		 */
		return apply_filters( 'llms_rest_prepare_quiz_object_response', $data, $quiz, $request );

	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since [version]
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );

		if ( ! empty( $request['parent'] ) ) {
			$parent_id = $request['parent'];
		}

		// Filter by parent.
		if ( ! empty( $parent_id ) ) {
			$query_args = array_merge(
				$query_args,
				array(
					'meta_query' => array(
						array(
							'key'     => '_llms_lesson_id',
							'value'   => $parent_id,
							'compare' => '=',
						),
					),
				)
			);
		}

		// Filter by course. Only makes sense if not already filtered by parent.
		if ( ! empty( $request['course'] ) ) {
			$course_id = $request['course'];
		}

		if ( empty( $parent_id ) && ! empty( $course_id ) ) {

			global $wpdb;

			$query_args['llms_join']  = " INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
			$query_args['llms_where'] = $wpdb->prepare(
				"
				AND (
					{$wpdb->postmeta}.meta_key = '_llms_lesson_id' AND {$wpdb->postmeta}.meta_value
					IN (
						SELECT DISTINCT(pm2.post_id)
						FROM {$wpdb->postmeta} as pm JOIN {$wpdb->postmeta} as pm2
						WHERE pm.meta_key='_llms_parent_course' AND pm.meta_value=%d
						AND pm2.meta_key='_llms_parent_section' AND pm2.meta_value=pm.post_id
					)
				)
				",
				$course_id
			);
		}

		return $query_args;

	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since [version]
	 *
	 * @param  array           $prepared Array of collection arguments.
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @return WP_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );

		$q = parent::get_objects_query( $prepared, $request );

		remove_filter( 'posts_join', array( $this, 'posts_join' ), 10 );
		remove_filter( 'posts_where', array( $this, 'posts_where' ), 10 );

		return $q;
	}

	/**
	 * Alter WP_Query join clause.
	 *
	 * @since [version]
	 *
	 * @param string   $join  Join clause.
	 * @param WP_Query $query WP_Query.
	 * @return string
	 */
	public function posts_join( $join, $query ) {
		return empty( $query->query['llms_join'] ) ? $join : $join . $query->query['llms_join'];
	}

	/**
	 * Alter WP_Query where clause.
	 *
	 * @since [version]
	 *
	 * @param string   $where Where clause.
	 * @param WP_Query $query WP_Query.
	 * @return string
	 */
	public function posts_where( $where, $query ) {
		return empty( $query->query['llms_where'] ) ? $where : $where . $query->query['llms_where'];
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz $quiz LLMS Section.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $quiz ) {

		$links = parent::prepare_links( $quiz );

		unset( $links['content'] );

		$quiz_id   = $quiz->get( 'id' );
		$parent_id = $quiz->get( 'lesson_id' );
		$course    = $quiz->get_course();
		$course_id = $course ? $course->get( 'id' ) : null;

		$quiz_links = array();

		// Course.
		if ( $course_id ) {
			$quiz_links['course'] = array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'courses', $course_id ) ),
			);
		}

		// Lesson.
		if ( $parent_id ) {
			$quiz_links['parent'] = array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'lessons', $parent_id ) ),
			);
		}

		$links = array_merge( $links, $quiz_links );

		/**
		 * Filters the quiz's links.
		 *
		 * @since [version]
		 *
		 * @param array     $links Links for the given quiz.
		 * @param LLMS_Quiz $quiz  Quiz object.
		 */
		return apply_filters( 'llms_rest_quiz_links', $links, $quiz );

	}

	/**
	 * Checks if a Quiz can be read.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz $quiz The Quiz oject.
	 * @return bool Whether the post can be read.
	 *
	 * @todo Implement read permission based on the lesson's id:
	 * 0 indicates an "orphaned" quiz which can be edited and viewed by instructors and admins but cannot be read by students.
	 */
	protected function check_read_permission( $quiz ) {

		/**
		 * As of now, quizs of password protected lessons cannot be read.
		 */
		if ( post_password_required( $quiz->get_course() ) ) {
			return false;
		}

		/**
		 * As of now, quizs of password protected courses cannot be read.
		 */
		if ( post_password_required( $quiz->get_course() ) ) {
			return false;
		}

		/**
		 * At the moment we grant quizs read permission only to who can edit quizs.
		 */
		return parent::check_update_permission( $quiz );

	}

}
