<?php

namespace Test\App\Transformer;

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Transformer\RatingTransformer;
use TestCase;

class RatingTransformerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var RatingTransformer
     */
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new RatingTransformer();
    }

    public function test_it_can_be_initialized()
    {
        $this->assertInstanceOf(RatingTransformer::class, $this->subject);
    }

    public function test_it_can_transform_a_rating_for_an_author()
    {
        $author = factory(\App\Author::class)->create();
        $rating = $author->ratings()->save(
            factory('App\Rating')->make()
        );

        $actual = $this->subject->transform($rating);

        $this->assertEquals($rating->id, $actual['id']);
        $this->assertEquals($rating->value, $actual['value']);
        $this->assertEquals($rating->rateable_type, $actual['type']);
        $this->assertEquals($rating->created_at->toIso8601String(), $actual['created']);
        $this->assertEquals($rating->updated_at->toIso8601String(), $actual['updated']);

        $this->assertArrayHasKey('links', $actual);
        $links = $actual['links'];
        $this->assertCount(1, $links);

        $authorLink = $links[0];
        $this->assertArrayHasKey('rel', $authorLink);
        $this->assertEquals('author', $authorLink['rel']);
        $this->assertArrayHasKey('href', $authorLink);
        $this->assertEquals(
            route('authors.show', ['id' => $author->id]),
            $authorLink['href']
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Rateable model type for Foo\Bar is not defined
     */
    public function test_it_throws_an_exception_when_a_model_is_not_defined()
    {
        $rating = factory(\App\Rating::class)->create([
            'value' => 5,
            'rateable_type' => 'Foo\Bar',
            'rateable_id' => 1
        ]);

        $this->subject->transform($rating);
    }
}
