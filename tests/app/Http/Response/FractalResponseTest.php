<?php

namespace Tests\App\Http\Response;

use App\Http\Response\FractalResponse;
use League\Fractal\Manager;
use Mockery as m;
use Illuminate\Http\Request;
use League\Fractal\Serializer\SerializerAbstract;
use TestCase;

class FractalResponseTest extends TestCase
{
    public function test_it_can_be_initialized()
    {
        $manager = m::mock(Manager::class);
        $serializer = m::mock(SerializerAbstract::class);
        $request = m::mock(Request::class);

        $manager->shouldReceive('setSerializer')
                ->with($serializer)
                ->once()
                ->andReturn($manager);
        
        $fractal = new FractalResponse($manager, $serializer, $request);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }

    public function test_it_can_transform_an_item()
    {
        $request = m::mock(Request::class);
        // Transform
        $transformer = m::mock('League\Fractal\TransformerAbstract');

        // Scope
        $scope = m::mock('League\Fractal\Scope');
        $scope->shouldReceive('toArray')
            ->once()
            ->andReturn(['foo' => 'bar']);

        // Serializer
        $serializer = m::mock('League\Fractal\Serializer\SerializerAbstract');

        $manager = m::mock('League\Fractal\Manager');
        $manager->shouldReceive('setSerializer')
                ->with($serializer)
                ->once();

        $manager->shouldReceive('createData')
                ->once()
                ->andReturn($scope);

        $subject = new FractalResponse($manager, $serializer, $request);
        $this->assertInternalType(
            'array',
            $subject->item(['foo' => 'bar'], $transformer)
        );
    }

    public function test_it_can_transform_a_collection()
    {
        $data = [
            ['foo' => 'bar'],
            ['fizz' => 'buzz'],
        ];

        $request = m::mock(Request::class);
        // Transformer
        $transformer = m::mock('League\Fractal\TransformerAbstract');

        // Scope
        $scope = m::mock('League\Fractal\Scope');
        $scope->shouldReceive('toArray')
            ->once()
            ->andReturn($data);

        // Serializer
        $serializer = m::mock('League\Fractal\Serializer\SerializerAbstract');

        $manager = m::mock('League\Fractal\Manager');
        $manager->shouldReceive('setSerializer')
                ->with($serializer)
                ->once();

        $manager->shouldReceive('createData')
                ->once()
                ->andReturn($scope);

        $subject = new FractalResponse($manager, $serializer, $request);
        $this->assertInternalType(
            'array',
            $subject->collection($data, $transformer)
        );
    }
}
