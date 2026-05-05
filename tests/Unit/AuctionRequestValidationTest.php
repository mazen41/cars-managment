<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\CreateAuctionRequest;
use App\Http\Requests\RequestAuctionRequest;
use App\Models\Car;
use App\Models\User;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class AuctionRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $seller;
    protected $car;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->seller = User::factory()->customer()->create();
        $this->car = Car::factory()->create([
            'user_id' => $this->seller->id,
            'moderation_status' => CarModerationStatusEnum::PUBLISHED,
            'car_status' => CarStatusEnum::AVAILABLE,
            'photos' => ['photo1.jpg', 'photo2.jpg']
        ]);
    }

    public function test_create_auction_request_authorization_passes_for_admin()
    {
        $this->actingAs($this->admin);
        
        $request = new CreateAuctionRequest();
        $request->setUserResolver(function () {
            return $this->admin;
        });
        
        $this->assertTrue($request->authorize());
    }

    public function test_create_auction_request_authorization_fails_for_non_admin()
    {
        $this->actingAs($this->seller);
        
        $request = new CreateAuctionRequest();
        $request->setUserResolver(function () {
            return $this->seller;
        });
        
        $this->assertFalse($request->authorize());
    }

    public function test_request_auction_request_authorization_passes_for_user()
    {
        $this->actingAs($this->seller);
        
        $request = new RequestAuctionRequest();
        $request->setUserResolver(function () {
            return $this->seller;
        });
        
        $this->assertTrue($request->authorize());
    }

    public function test_request_auction_validation_passes_with_valid_data()
    {
        $this->actingAs($this->seller);
        
        $data = [
            'car_id' => $this->car->id,
            'requested_reserve_price' => 5000,
            'preferred_duration' => 24,
            'notes' => 'Please consider this car for auction'
        ];

        $request = new RequestAuctionRequest();
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes(), 'Validation should pass with valid data. Errors: ' . json_encode($validator->errors()->all()));
    }

    public function test_request_auction_validation_fails_with_invalid_reserve_price()
    {
        $this->actingAs($this->seller);
        
        $data = [
            'car_id' => $this->car->id,
            'requested_reserve_price' => 50, // Too low according to ValidReservePrice rule
            'preferred_duration' => 24,
        ];

        $request = new RequestAuctionRequest();
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requested_reserve_price', $validator->errors()->toArray());
    }
}