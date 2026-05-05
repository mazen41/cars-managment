<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use \SmsGateway24\SmsGateway24;
use Carbon\Carbon;

class SmsDeviceCheck extends Check

{

    public function run(): Result

    {

        $gateway = new SmsGateway24(env('SMS24GATEWAY_API_KEY'));

        $smsDeviceId = env('SMS24GATEWAY_DEVICE_ID');

        $statusResult = $gateway->getDeviceStatus($smsDeviceId);
        $status = isset($statusResult->title) ? $statusResult->title : null;
        $lastSeen = isset($statusResult->lastseen) ? $statusResult->lastseen->date : null;
        $diffForHumans = Carbon::parse($lastSeen)->diffForHumans();
        $formattedLastSeen = Carbon::parse($lastSeen)->format('Y-m-d H:i:s');
            $result = Result::make()
                ->meta([
                    'device_id' => $smsDeviceId,
                ])->shortSummary("Device status: $status. Last seen:  $formattedLastSeen");

        if (!$status) {
            return $result->failed('Unable to retrieve SMS device status');
        }
        if ($status === 'online') {
            return $result->ok();

        }
        if ($status === 'offline') {
            return $result->failed("Last seen over $diffForHumans");

        } elseif ($status === 'recently') {
            return $result->warning("Last seen $diffForHumans");
        }

        return $result->ok();

    }

}
