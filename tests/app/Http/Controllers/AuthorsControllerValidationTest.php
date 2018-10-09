<?php

namespace Tests\App\Http\Controllers;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use TestCase;

class AuthorsControllerValidationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function store_method_validates_required_fields()
    {
        $this->post('/authors', [], $this->header_request_accept_type);

        $data = $this->response->getData(true);

        $fields = ['name','gender','biography'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    /** @test */
    public function store_invalidates_incorrect_gender_data()
    {
        $postData = [
            'name' => 'John Doe',
            'gender' => 'unknown',
            'biography' => 'An anonymous author'
        ];

        $this->post('/authors', $postData, $this->header_request_accept_type);

        $this->seeStatusCode(422);
        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('gender', $data);
        $this->assertEquals(["Gender format is invalid: must equal 'male' or 'female'"], $data['gender']);
    }

    public function test_validation_is_valid_when_name_is_just_long_enough()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['name'] = str_repeat('a', 256);

            $this->{$method}($test['url'], $test['data'], $this->header_request_accept_type);

            $this->seeStatusCode(422);

            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertEquals(["The name may not be greater than 255 characters."], $data['name']);
        }
    }

    /** @test */
    public function store_is_valid_when_name_is_just_long_enough()
    {
        $postData = [
            'name' => str_repeat('a', 255),
            'gender' => 'male',
            'biography' => 'A Valid author'
        ];

        $this->post('/authors', $postData, $this->header_request_accept_type);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeInDatabase('authors', $postData);
    }

    /** @test */
    public function update_method_validates_required_fields()
    {
        $author = factory(\App\Author::class)->create();
        $this->put("/authors/{$author->id}", [], $this->header_request_accept_type);
        $this->seeStatusCode(422);
        $data = $this->response->getData(true);

        $fields = ['name', 'gender', 'biography'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    /** @test **/
    public function validation_validates_required_fields()
    {
        $author = factory(\App\Author::class)->create();
        $tests = [
            ['method' => 'post', 'url' => '/authors'],
            ['method' => 'put', 'url' => "/authors/{$author->id}"],
        ];
        foreach ($tests as $test) {
            $method = $test['method'];
            $this->{$method}($test['url'], [], $this->header_request_accept_type);
            $this->seeStatusCode(422);
            $data = $this->response->getData(true);
            $fields = ['name', 'gender', 'biography'];
            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $data);
                $this->assertEquals(["The {$field} field is required."], $data[$field]);
            }
        }
    }

    /** @test */
    public function validation_invalidates_incorrect_gender_data()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['gender'] = 'unknown';
            $this->{$method}($test['url'], $test['data'], $this->header_request_accept_type);

            $this->seeStatusCode(422);

            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('gender', $data);
            $this->assertEquals(
                ["Gender format is invalid: must equal 'male' or 'female'"],
                $data['gender']
            );
        }
    } // end function validation_invalidates_incorrect_gender_data()

    /**
     * Provides boilerplate test istructions for validation.
     * @return array
     */
    private function getValidationTestData()
    {
        $author = factory(\App\Author::class)->create();

        return [
            // Create
            [
                'method' => 'post',
                'url' => '/authors',
                'status' => 201,
                'data' => [
                    'name' => 'John Doe',
                    'biography' => 'An anonymous author',
                    'gender' => 'male'
                ]
            ],

            // Update
            [
                'method' => 'put',
                'url' => "/authors/{$author->id}",
                'status' => 200,
                'data' => [
                    'name' => $author->name,
                    'biography' => $author->biography,
                    'gender' => $author->gender
                ]
            ]
        ]; // end $tests = []
    }
}
