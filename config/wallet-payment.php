<?php
/*
This config file has all of the yemeni wallets credintials
*/

return[
'jaib' =>[
    'user'=>env('JAIB_USER'),
    'pass'=>env('JAIB_PASS'),
    'agent_code'=>env('JAIB_AGENT_CODE'),
],

'floosak'=> [
'short_code'=>env('FLOOSAK_SHORT_CODE'),
'phone'=>env('FLOOSAK_PHONE_NUMBER'),
'api_key'=>env('FLOOSAK_API_KEY'),
],

'jawali'=> [
    'user'=>env('JAWALI_USERNAME'),
    'pass'=>env('JAWALI_PASS'),
    'user_id'=>env('JAWALI_USERNAME'),
    'org_id'=>env('JAWALI_ORGID'),
    'agent_id'=>env('JAWALI_AGENT_ID'),
    'agent_pwd'=>env('JAWALI_AGENT_PWD'),
],
];
