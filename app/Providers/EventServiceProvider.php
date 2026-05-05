<?php

namespace App\Providers;

use App\Events\AccountDeletionRequested;
use App\Events\AuctionInsuranceDepositPaid;
use App\Events\AuctionInsuranceDepositRefundRequested;
use App\Events\AuctionInvoicePaid;
use App\Events\CustomerRegistered;
use App\Events\MessageSent;
use App\Listeners\NewMessageListener;
use App\Listeners\SendAccountDeletionRequestNotification;
use App\Listeners\SendNewCustomerNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\CarReservationPaid;
use App\Events\CarInspectionPaid;
use App\Listeners\CarInspectionCommission;
use App\Listeners\CarReservationCommission;
use App\Listeners\SendCarInspectionNotification;
use App\Listeners\SendCarReservationNotification;
use App\Listeners\SendCarInspectionAdminNotification;
use App\Listeners\SendCarReservationAdminNotification;
use App\Events\BidAccepted;
use App\Events\OfferReceived;
use App\Events\OfferAccepted;
use App\Events\OfferRejected;
use App\Events\ItemSold;
use App\Listeners\SendInsuranceDepositPaidNotification;
use App\Listeners\SendInsuranceDepositRefundRequestNotification;
use App\Listeners\SendOutbidNotification;
use App\Listeners\SendOfferReceivedNotification;
use App\Listeners\SendOfferAcceptedNotification;
use App\Listeners\SendOfferRejectedNotification;
use App\Listeners\SendItemSoldNotification;
use App\Listeners\AuctionInvoiceCommission;
use App\Listeners\SendAuctionInvoicePaidAdminNotification;
use App\Listeners\SendHighValueBidAdminNotification;
use App\Listeners\SendAuctionCompletedAdminNotification;
use App\Listeners\SendInsuranceDepositPaidAdminNotification;
use App\Listeners\SendInsuranceDepositRefundAdminNotification;
use App\Listeners\SendOfferSubmittedAdminNotification;
use App\Events\CarAdded;
use App\Events\CarStatusChanged;
use App\Events\CarModerationStatusChanged;
use App\Events\CarDeleted;
use App\Listeners\SendCarAddedNotification;
use App\Listeners\SendCarStatusChangedNotification;
use App\Listeners\SendCarModerationStatusChangedNotification;
use App\Listeners\SendCarDeletedNotification;
use App\Events\CarInspectorAssigned;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    Registered::class => [
      SendEmailVerificationNotification::class,
    ],
    CustomerRegistered::class => [
        SendNewCustomerNotification::class
    ],
    AccountDeletionRequested::class => [
        SendAccountDeletionRequestNotification::class,
    ],
    MessageSent::class => [
        NewMessageListener::class,
    ],
    CarReservationPaid::class => [
       CarReservationCommission::class,
       SendCarReservationNotification::class,
       SendCarReservationAdminNotification::class,
    ],
    CarInspectionPaid::class => [
       CarInspectionCommission::class,
       SendCarInspectionNotification::class,
       SendCarInspectionAdminNotification::class,
    ],
    CarInspectorAssigned::class => [
       CarInspectionCommission::class,
    ],
    AuctionInvoicePaid::class => [
         AuctionInvoiceCommission::class,
         SendAuctionInvoicePaidAdminNotification::class,
    ],
    AuctionInsuranceDepositPaid::class => [
        SendInsuranceDepositPaidNotification::class,
        SendInsuranceDepositPaidAdminNotification::class
    ],
    AuctionInsuranceDepositRefundRequested::class => [
        SendInsuranceDepositRefundRequestNotification::class,
        SendInsuranceDepositRefundAdminNotification::class
    ],
    BidAccepted::class => [
       SendOutbidNotification::class,
       SendHighValueBidAdminNotification::class,
    ],
    OfferReceived::class => [
       SendOfferReceivedNotification::class,
       SendOfferSubmittedAdminNotification::class,
    ],
    OfferAccepted::class => [
       SendOfferAcceptedNotification::class,
    ],
    OfferRejected::class => [
       SendOfferRejectedNotification::class,
    ],
    ItemSold::class => [
       SendItemSoldNotification::class,
       SendAuctionCompletedAdminNotification::class,
    ],
    CarAdded::class => [
       SendCarAddedNotification::class,
    ],
    CarStatusChanged::class => [
       SendCarStatusChangedNotification::class,
    ],
    CarModerationStatusChanged::class => [
       SendCarModerationStatusChangedNotification::class,
    ],
    CarDeleted::class => [
       SendCarDeletedNotification::class,
    ],
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();

    //
  }
}
