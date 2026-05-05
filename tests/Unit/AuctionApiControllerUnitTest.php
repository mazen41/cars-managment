<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\V2\AuctionController;
use App\Http\Requests\PlaceBidRequest;
use App\Http\Requests\RequestAuctionRequest;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionRequest;
use App\Models\User;
use App\Services\AuctionService;
use App\Services\BiddingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AuctionApiControllerUnitTest extends TestCase
{
    protected $auctionService;
    protected $biddingService;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auctionService = Mockery::mock(AuctionService::class);
        $this->biddingService = Mockery::mock(BiddingService::class);
        $this->controller = new AuctionController($this->auctionService, $this->biddingService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_successful_response()
    {
        // Mock the Auction model query
        $mockAuctions = new LengthAwarePaginator([], 0, 15);
        
        // Mock Eloquent query builder
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('when')->andReturnSelf();
        $mockQuery->shouldReceive('orderBy')->andReturnSelf();
        $mockQuery->shouldReceive('paginate')->andReturn($mockAuctions);

        // Mock the Auction model
        Auction::shouldReceive('with')->andReturn($mockQuery);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Auctions retrieved successfully', $responseData['message']);
    }

    public function test_show_returns_auction_details()
    {
        $auction = Mockery::mock(Auction::class);
        $auction->shouldReceive('load')->andReturnSelf();
        $auction->shouldReceive('getAttribute')->with('bids')->andReturn(Mockery::mock());
        $auction->bids = Mockery::mock();
        $auction->bids->shouldReceive('count')->andReturn(5);
        $auction->bids->shouldReceive('distinct')->andReturn(Mockery::mock());
        $auction->bids->shouldReceive('count')->andReturn(3);
        
        $auction->current_bid = 1000;
        $auction->end_time = now()->addHour();
        $auction->status = 'active';

        Auth::shouldReceive('check')->andReturn(false);

        $response = $this->controller->show($auction);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Auction details retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('auction', $responseData['data']);
        $this->assertArrayHasKey('bid_stats', $responseData['data']);
    }

    public function test_place_bid_calls_bidding_service()
    {
        $auction = Mockery::mock(Auction::class);
        $user = Mockery::mock(User::class);
        $bid = Mockery::mock(AuctionBid::class);
        
        $bid->shouldReceive('load')->andReturnSelf();
        $auction->shouldReceive('fresh')->andReturnSelf();

        Auth::shouldReceive('user')->andReturn($user);

        $request = Mockery::mock(PlaceBidRequest::class);
        $request->shouldReceive('validated')->andReturn(['amount' => 1500]);

        $this->biddingService
            ->shouldReceive('placeBid')
            ->once()
            ->with($auction, $user, 1500)
            ->andReturn($bid);

        $response = $this->controller->placeBid($auction, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Bid placed successfully', $responseData['message']);
    }

    public function test_request_auction_creates_auction_request()
    {
        $user = Mockery::mock(User::class);
        $user->id = 1;

        Auth::shouldReceive('user')->andReturn($user);

        $request = Mockery::mock(RequestAuctionRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'car_id' => 1,
            'requested_reserve_price' => 5000,
            'preferred_duration' => 48,
            'notes' => 'Test notes'
        ]);

        $auctionRequest = Mockery::mock(AuctionRequest::class);
        $auctionRequest->shouldReceive('load')->andReturnSelf();

        AuctionRequest::shouldReceive('create')->andReturn($auctionRequest);

        $response = $this->controller->requestAuction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Auction request submitted successfully', $responseData['message']);
    }

    public function test_get_bid_history_returns_paginated_bids()
    {
        $auction = Mockery::mock(Auction::class);
        $mockBids = new LengthAwarePaginator([], 0, 20);
        
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('orderBy')->andReturnSelf();
        $mockQuery->shouldReceive('paginate')->andReturn($mockBids);

        $auction->shouldReceive('bids')->andReturn($mockQuery);

        $request = new Request();
        $response = $this->controller->getBidHistory($auction, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Bid history retrieved successfully', $responseData['message']);
    }

    public function test_get_my_bids_requires_authentication()
    {
        $user = Mockery::mock(User::class);
        $user->id = 1;

        Auth::shouldReceive('user')->andReturn($user);

        $mockBids = new LengthAwarePaginator([], 0, 15);
        
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('when')->andReturnSelf();
        $mockQuery->shouldReceive('orderBy')->andReturnSelf();
        $mockQuery->shouldReceive('paginate')->andReturn($mockBids);

        AuctionBid::shouldReceive('with')->andReturn($mockQuery);

        $request = new Request();
        $response = $this->controller->getMyBids($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('User bid history retrieved successfully', $responseData['message']);
    }

    public function test_get_featured_auctions_returns_high_bid_auctions()
    {
        $mockAuctions = collect([]);
        
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('orderBy')->andReturnSelf();
        $mockQuery->shouldReceive('limit')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn($mockAuctions);

        Auction::shouldReceive('with')->andReturn($mockQuery);

        $request = new Request();
        $response = $this->controller->getFeaturedAuctions($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Featured auctions retrieved successfully', $responseData['message']);
    }

    public function test_get_ending_soon_returns_time_filtered_auctions()
    {
        $mockAuctions = collect([]);
        
        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('with')->andReturnSelf();
        $mockQuery->shouldReceive('where')->andReturnSelf();
        $mockQuery->shouldReceive('orderBy')->andReturnSelf();
        $mockQuery->shouldReceive('limit')->andReturnSelf();
        $mockQuery->shouldReceive('get')->andReturn($mockAuctions);

        Auction::shouldReceive('with')->andReturn($mockQuery);

        $request = new Request();
        $response = $this->controller->getEndingSoon($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Ending soon auctions retrieved successfully', $responseData['message']);
    }

    public function test_controller_handles_exceptions_gracefully()
    {
        // Mock the Auction model to throw an exception
        Auction::shouldReceive('with')->andThrow(new \Exception('Database error'));

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Failed to retrieve auctions', $responseData['message']);
        $this->assertEquals('Database error', $responseData['error']);
    }
}