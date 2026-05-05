<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AdminAuctionController;
use App\Services\AuctionService;
use App\Services\BiddingService;
use App\Models\User;
use App\Models\Auction;
use App\Models\AuctionRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

class AdminAuctionControllerUnitTest extends TestCase
{
    use WithFaker;

    protected AdminAuctionController $controller;
    protected $auctionService;
    protected $biddingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auctionService = Mockery::mock(AuctionService::class);
        $this->biddingService = Mockery::mock(BiddingService::class);
        
        $this->controller = new AdminAuctionController(
            $this->auctionService,
            $this->biddingService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_controller_can_be_instantiated()
    {
        $this->assertInstanceOf(AdminAuctionController::class, $this->controller);
    }

    public function test_controller_has_required_methods()
    {
        $requiredMethods = [
            'index',
            'store',
            'show',
            'update',
            'destroy',
            'start',
            'cancel',
            'extend',
            'getRequests',
            'approveRequest',
            'rejectRequest',
            'getStatistics',
            'getAvailableCars',
            'getSellers'
        ];

        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists($this->controller, $method),
                "Controller is missing required method: {$method}"
            );
        }
    }

    public function test_controller_dependencies_are_injected()
    {
        $reflection = new \ReflectionClass($this->controller);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);
        
        $this->assertEquals('auctionService', $parameters[0]->getName());
        $this->assertEquals('biddingService', $parameters[1]->getName());
    }

    public function test_request_validation_classes_exist()
    {
        $requestClasses = [
            'App\Http\Requests\CreateAuctionRequest',
            'App\Http\Requests\UpdateAuctionRequest',
            'App\Http\Requests\CancelAuctionRequest',
            'App\Http\Requests\ExtendAuctionRequest',
            'App\Http\Requests\ApproveAuctionRequestRequest',
            'App\Http\Requests\RejectAuctionRequestRequest',
        ];

        foreach ($requestClasses as $class) {
            $this->assertTrue(
                class_exists($class),
                "Request validation class does not exist: {$class}"
            );
        }
    }

    public function test_all_request_classes_have_authorize_method()
    {
        $requestClasses = [
            'App\Http\Requests\CreateAuctionRequest',
            'App\Http\Requests\UpdateAuctionRequest',
            'App\Http\Requests\CancelAuctionRequest',
            'App\Http\Requests\ExtendAuctionRequest',
            'App\Http\Requests\ApproveAuctionRequestRequest',
            'App\Http\Requests\RejectAuctionRequestRequest',
        ];

        foreach ($requestClasses as $class) {
            $this->assertTrue(
                method_exists($class, 'authorize'),
                "Request class {$class} is missing authorize method"
            );
            
            $this->assertTrue(
                method_exists($class, 'rules'),
                "Request class {$class} is missing rules method"
            );
        }
    }
}