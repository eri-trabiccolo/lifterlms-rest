<?php
/**
 * Tests for Quizzes API.
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_quizzes
 *
 * @since [version]
 * @version [version]
 */
class LLMS_REST_Test_Quizzes extends LLMS_REST_Unit_Test_Case_Posts {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/quizzes';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_quiz';


	/**
	 * Array of link $rels expected for each item.
	 *
	 * @var array
	 */
	private $expected_link_rels = array(
		'self',
		'collection',
		'course',
		'parent',
	);

	/**
	 * Array of schema properties.
	 *
	 * @var array
	 */
	private $schema_props = array(
		'id',
		'attempt_limiting',
		'attempts_allowed',
		'content',
		'title',
		'course_id',
		'date_created',
		'date_created_gmt',
		'date_updated',
		'date_updated_gmt',
		'parent_id',
		'passing_percentage',
		'permalink',
		'post_type',
		'randomize_questions',
		'show_correct_answer',
		'slug',
		'status',
		'time_limit',
		'time_limiting',
		'total_points'
	);

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->user_allowed = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		$this->user_forbidden = $this->factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);

		$this->endpoint = new LLMS_REST_Quizzes_Controller();

	}


	/**
	 * Test route registration.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @return void
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)', $routes );
	}

	/**
	 * Test the item schema.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_item_schema() {

		$schema = $this->endpoint->get_item_schema();

		$this->assertEquals( 'llms_quiz', $schema['title'] );

		$props = $this->schema_props;

		$schema_keys = array_keys( $schema['properties'] );
		sort( $schema_keys );
		sort( $props );

		$this->assertEquals( $props, $schema_keys );

		// Check quiz status enum.
		$this->assertEquals(
			array(
				'publish',
				'draft'
			),
			$schema['properties']['status']['enum']
		);

	}

	// public function test_get_items_success() {}

	/**
	 * Test filtering by status.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_filter_by_status() {

		// create course with 1 section, 2 lessons, and 2 quizzes.
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 2
			)
		);

		$quizzes = $course->get_quizzes();

		// set the second quiz status to `draft`.
		llms_get_post( $quizzes[1] )->set( 'status', 'draft' );

		// Filter by `draft` status.
		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'status' => 'draft',
			)
		);
		$res_data = $res->get_data();

		// Expect 1 result.
		$this->assertEquals( 1, count( $res_data ) );
		// Expect the returned quiz is actually the second quiz.
		$this->assertEquals( llms_get_post( $quizzes[1] ), llms_get_post( $res_data[0]['id'] ) );

		// Filter by `publish` status.
		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'status' => 'publish',
			)
		);
		$res_data = $res->get_data();

		// Expect 1 result.
		$this->assertEquals( 1, count( $res_data ) );
		// Expect the returned quiz is actually the second quiz.
		$this->assertEquals( llms_get_post( $quizzes[0] ), llms_get_post( $res_data[0]['id'] ) );
	}

	/**
	 * Test filtering by parent.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_filter_by_parent() {

		wp_set_current_user( $this->user_allowed );

		// create 1 course with 1 section, 2 lessons, and 2 quizzes (1 per lesson).
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 2
			)
		);
		$lessons = $course->get_lessons('ids');

		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'parent' => $lessons[1],
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $res );
		$res_data = $res->get_data();

		// Expect 1 result.
		$this->assertEquals( 1, count( $res_data ) );
		// Expect the returned quiz is actually the quiz of the second lesson.
		$this->assertEquals( llms_get_post( $lessons[1] )->get_quiz(), llms_get_post( $res_data[0]['id'] ) );

		// Filter by non existing parent.
		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'parent' => $lessons[1] + 7894,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $res );

		// Expect 0 results.
		$res_data = $res->get_data();
		$this->assertEquals( 0, count( $res_data ) );

	}

	/**
	 * Test filtering by course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_filter_by_course() {

		wp_set_current_user( $this->user_allowed );

		// create 2 course with 1 section, 2 lessons, and 2 quizzes (1 per lesson).
		$course_ids = $this->factory->course->create_many(
			2,
			array(
				'sections' => 1,
				'lessons'  => 2,
				'quizzes'  => 2
			)
		);

		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'course' => $course_ids[1],
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $res );
		$res_data = $res->get_data();

		// Expect 2 results.
		$this->assertEquals( 2, count( $res_data ) );
		// Expect the returned quizzes are actually the quizzes of the second course.
		$this->assertEquals(
			llms_get_post( $course_ids[1] )->get_quizzes(),
			wp_list_pluck( $res_data, 'id' )
		);

		// Filter by non existing course
		$res = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'parent' => $course_ids[1] + 7894,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $res );

		// Expect 0 results.
		$res_data = $res->get_data();
		$this->assertEquals( 0, count( $res_data ) );

	}

	// public function test_get_items_exclude() {}
	// public function test_get_items_include() {}
	// public function test_get_items_orderby_id() {}
	// public function test_get_items_orderby_title() {}

	// public function test_get_items_orderby_order() {}

	// public function test_get_items_orderby_date_created() {}
	// public function test_get_items_orderby_date_updated() {}

	// public function test_get_items_pagination() {}

	// public function test_create_item_success() {}
	// public function test_create_item_wrong_params() {}
	// public function test_create_item_missing_required() {}
	// public function test_create_item_auth_errors() {}

	public function test_get_item_success() {

		// create course with 1 section, 1 lesson, and 1 quiz.
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 1
			)
		);

		$quizzes = $course->get_quizzes();

		$res = $this->perform_mock_request(
			'GET',
			"{$this->route}/{$quizzes[0]}"
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $res );

		$res_data = $res->get_data();

		// Check all the expected props have been returned.
		$props = $this->schema_props;
		$schema_keys = array_keys( $res_data );
		sort( $schema_keys );
		sort( $props );

		$this->assertEquals( $props, $schema_keys );

		$this->assertEquals( llms_get_post( $quizzes[0] ), llms_get_post( $res_data['id'] ) );
	}

	// public function test_get_item_auth_errors() {}

	/**
	 * Test not found quiz.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_item_not_found() {

		wp_set_current_user( $this->user_allowed );

		$res = $this->perform_mock_request( 'GET', sprintf( '%1$s/%2$d', $this->route, 1234 ) );
		$this->assertResponseStatusEquals( 404, $res );
		$this->assertResponseCodeEquals( 'llms_rest_not_found', $res );

	}

	// public function test_update_item_success() {}
	// public function test_update_item_auth_errors() {}
	// public function test_update_item_errors() {}


	// public function test_delete_item_success() {}
	// public function test_delete_item_auth_errors() {}

	/**
	 * Test links.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @return void
	 */
	public function test_links() {

		wp_set_current_user( $this->user_allowed );

		// create course with 1 section, 1 lessons, and 1 quiz.
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 1
			)
		);

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course->get_quizzes()[0]
		);

		$this->assertEquals( $this->expected_link_rels, array_keys( $response->get_links() ) );

	}

}
