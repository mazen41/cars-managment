<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Services\AuctionConfigurationService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuctionConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        Permission::create(['name' => 'auction_configuration']);
        
        // Create admin role and assign permission
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo('auction_configuration');
    }

    /** @test */
    public function admin_can_access_auction_configuration_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
                         ->get(route('auction_configuration.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.setup_configurations.auction_configuration.index');
    }

    /** @test */
    public function admin_can_access_auction_notifications_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
                         ->get(route('auction_notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.setup_configurations.auction_configuration.notifications');
    }

    /** @test */
    public function admin_can_update_auction_configuration()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $configData = [
            'default_bid_increment' => 150.00,
            'default_auction_duration' => 48,
            'auto_extend_enabled' => 1,
            'auto_extend_duration' => 180,
            'max_extensions' => 3,
            'bid_rate_limit' => 15,
            'auction_ending_notification_minutes' => 15,
            'require_verification' => 1,
            'allow_seller_requests' => 0,
        ];

        $response = $this->actingAs($admin)
                         ->post(route('auction_configuration.update'), $configData);

        $response->assertRedirect();
        $response->assertSessionHas('flash_notification');

        // Verify configuration was saved
        $this->assertEquals(150.00, get_setting('auction_default_bid_increment'));
        $this->assertEquals(48, get_setting('auction_default_duration_hours'));
        $this->assertEquals(1, get_setting('auction_auto_extend_enabled'));
    }

    /** @test */
    public function admin_can_update_notification_templates()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $templateData = [
            'auction_bid_placed_template' => 'Custom bid placed message: {bid_amount} on {auction_title}',
            'auction_outbid_template' => 'Custom outbid message for {user_name}',
            'auction_ending_template' => 'Custom ending message: {time_remaining} left',
        ];

        $response = $this->actingAs($admin)
                         ->post(route('auction_notifications.update'), $templateData);

        $response->assertRedirect();
        $response->assertSessionHas('flash_notification');

        // Verify templates were saved
        $this->assertEquals(
            'Custom bid placed message: {bid_amount} on {auction_title}',
            get_setting('auction_bid_placed_template')
        );
    }

    /** @test */
    public function auction_configuration_service_returns_correct_values()
    {
        // Set some test configuration values
        \App\Models\BusinessSetting::create([
            'type' => 'auction_default_bid_increment',
            'value' => '200.00'
        ]);

        \App\Models\BusinessSetting::create([
            'type' => 'auction_auto_extend_enabled',
            'value' => '1'
        ]);

        $config = AuctionConfigurationService::getConfig();

        $this->assertEquals(200.00, $config['default_bid_increment']);
        $this->assertTrue($config['auto_extend_enabled']);
    }

    /** @test */
    public function auction_configuration_validation_works()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Test with invalid data
        $invalidData = [
            'default_bid_increment' => 0, // Should be at least 1
            'default_auction_duration' => 200, // Should be max 168
            'auto_extend_duration' => 10, // Should be at least 30
            'max_extensions' => 15, // Should be max 10
            'bid_rate_limit' => 0, // Should be at least 1
            'auction_ending_notification_minutes' => 100, // Should be max 60
        ];

        $response = $this->actingAs($admin)
                         ->post(route('auction_configuration.update'), $invalidData);

        $response->assertSessionHasErrors([
            'default_bid_increment',
            'default_auction_duration',
            'auto_extend_duration',
            'max_extensions',
            'bid_rate_limit',
            'auction_ending_notification_minutes'
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_auction_configuration()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get(route('auction_configuration.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function api_returns_auction_configuration()
    {
        $response = $this->get('/api/v2/auction-configuration');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'default_bid_increment',
                'default_duration_hours',
                'auto_extend_enabled',
                'auto_extend_duration',
                'max_extensions',
                'bid_rate_limit',
                'ending_notification_minutes',
                'require_verification',
                'allow_seller_requests'
            ]
        ]);
    }

    /** @test */
    public function api_returns_notification_templates()
    {
        $response = $this->get('/api/v2/auction-notification-templates');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'bid_placed',
                'outbid',
                'ending',
                'ended',
                'won',
                'time_extended'
            ]
        ]);
    }
}