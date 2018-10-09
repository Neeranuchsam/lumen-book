<?php

namespace Tests\App\Http\Controllers;

use App\Transformer\AuthorTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use TestCase;

class AuthorsControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->subject = new AuthorTransformer();
    }

    /** @test */
    public function index_responds_with_200_status_code()
    {
        $this->get('/authors')->seeStatusCode(Response::HTTP_OK);
    }

    /** @test */
    public function index_should_return_a_collection_of_records()
    {
        $authors = factory('App\Author', 2)->create();

        $this->get('/authors', ['Accept' => 'application/json']);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);

        foreach ($authors as $author) {
            $this->seeJson([
                'id' => $author->id,
                'name' => $author->name,
                'gender' => $author->gender,
                'biography' => $author->biography,
                'created' => $author->created_at->toIso8601String(),
                'updated' => $author->updated_at->toIso8601String(),
            ]);
        }
    }

    /** @test */
    public function show_should_return_a_valid_author()
    {
        $book = $this->bookFactory();
        $author = $book->author;

        $this->get("/authors/{$author->id}", ['Accept' => 'application/json']);
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
            'gender' => $author->gender,
            'biography' => $author->biography,
            'created' => $author->created_at->toIso8601String(),
            'updated' => $author->updated_at->toIso8601String(),
        ]);
    }

    /** @test */
    public function show_should_fail_on_an_invalid_author()
    {
        $this->get('/authors/1234', ['Accept' => 'application/json']);
        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'message' => 'Not Found',
            'status' => Response::HTTP_NOT_FOUND
        ]);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('error', $body);
        $error = $body['error'];

        $this->assertEquals('Not Found', $error['message']);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $error['status']);
    }

    /** @test */
    public function it_can_transform_related_books()
    {
        $book = $this->bookFactory();
        $author = $book->author;
    
        $data = $this->subject->includeBooks($author);
        $this->assertInstanceOf(\League\Fractal\Resource\Collection::class, $data);
    }

    /** @test */
    public function show_optionally_includes_books()
    {
        $book = $this->bookFactory();
        $author = $book->author;

        $this->get(
            "/authors/{$author->id}?include=books",
            ['Accept' => 'application/json']
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('data', $data['books']);
        $this->assertCount(1, $data['books']['data']);

        // See Author Data
        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
        ]);

        // Test included book Data (the first record)
        $actual = $data['books']['data'][0];
        $this->assertEquals($book->id, $actual['id']);
        $this->assertEquals($book->title, $actual['title']);
        $this->assertEquals($book->description, $actual['description']);
        $this->assertEquals($book->created_at->toIso8601String(), $actual['created']);
        $this->assertEquals($book->updated_at->toIso8601String(), $actual['updated']);
    }

    /** @test */
    public function store_can_create_a_new_author()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer',
        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);

        $this->seeStatusCode(201);
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('authors', $postData);
    }

    /** @test */
    public function store_returns_a_valid_location_header()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer',
        ];

        $this->post('/authors', $postData, $this->header_request_accept_type);
        $this->seeStatusCode(201);

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        // Check the Location header
        $id = $data['data']['id'];
        $this->seeHeaderWithRegExp('Location', "/\/authors\/[\d]+$/");
    }

    /** @test */
    public function update_can_update_an_existing_author()
    {
        $author = factory('App\Author')->create();

        $requestData = [
            'name' => 'New Author Name',
            'gender' => $author->gender === 'male' ? 'female' : 'male',
            'biography' => 'An Updated Biography',
        ];

        $this->put(
            "/authors/{$author->id}",
            $requestData,
            $this->header_request_accept_type
        );
        $this->seeStatusCode(200)
            ->seeJson($requestData)
            ->seeInDatabase('authors', ['name' => 'New Author Name'])
            ->notSeeInDatabase('authors', ['name' => $author->name]);
        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_delete_can_remove_an_author_and_his_or_her_books()
    {
        $author = factory('App\Author')->create();

        $this->delete("/authors/{$author->id}")
            ->seeStatusCode(204)
            ->notSeeInDatabase('authors', ['id' => $author->id])
            ->notSeeInDatabase('books', ['author_id' => $author->id]);
    }

    public function test_deleting_an_invalid_author_should_return_a_404()
    {
        $this->delete('/authors/99999999999', [], $this->header_request_accept_type)
            ->seeStatusCode(404);
    }
}
