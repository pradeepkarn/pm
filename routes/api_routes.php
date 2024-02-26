<?php
$api_routes = [
    "/api/v1/account/create/{ug}" => 'Users_api@create_account@name.createAccountApi',
    "/api/v1/account/update/{ug}" => 'Users_api@update_account@name.createAccountApi',
    "/api/v1/account/login/{ug}" => 'Users_api@login@name.loginAccountApi',
    "/api/v1/account/login-via-token/{ug}" => 'Users_api@login_via_token@name.loginAccountViaTokenApi',
    "/api/v1/account/set-user-online/{ug}" => 'Users_api@set_user_online@name.setUserOnlineApi',
    "/api/v1/account/generate-temp-pass/{ug}" => 'Users_api@generate_temp_password@name.generateTempPassApi',

    "/api/v1/fuels/show-fuels" => 'Fuels_api@get_fules@name.getFuelsApi',

    "/api/v1/orders/list" => 'Orders_api@fetch_orders@name.fetchOrdersApi',
    "/api/v1/orders/driver/accept" => 'Orders_api@accept_order@name.acceptOrderApi',
    "/api/v1/orders/driver/status-change/{delivery_status}" => 'Orders_api@status_update_order@name.statustOrderApi',

    "/api/v1/orders/driver/history" => 'Orders_api@order_history@name.orderHistoryApi',
    "/api/v1/orders/driver/running" => 'Orders_api@running_orders@name.orderRunningApi',
    "/api/v1/orders/driver/task-history" => 'Orders_api@task_history@name.orderTaskHistoryApi',

    // version 2
    "/api/v2/orders/list" => 'Orders_v2_api@fetch_orders@name.fetchOrdersApiV2',
    "/api/v2/orders/driver/accept" => 'Orders_v2_api@accept_order@name.acceptOrderApiV2',
    "/api/v2/orders/driver/history" => 'Orders_v2_api@order_history@name.orderHistoryApiV2',
    "/api/v2/orders/driver/assigned" => 'Orders_v2_api@assigned_orders@name.orderAssignedApiV2',
    "/api/v2/orders/driver/picked-orders" => 'Orders_v2_api@picked_orders@name.orderPickedApiV2',
    "/api/v2/orders/driver/running" => 'Orders_v2_api@running_orders@name.orderRunningApiV2',
    "/api/v2/orders/driver/status-change/{delivery_status}" => 'Orders_v2_api@status_update_order@name.statustOrderApiV2',


    "/api/v1/account/update/location" => 'Orders_api@update_location@name.updateLocationApi',

    '/api/orders/update-single-order' => 'Orders_api_ctrl@update_on_purchase_event_from_client@name.updateSingleOrder',
   
    '/api/supports/create/ticket/{ug}' => 'Orders_api@create_ticket@name.supportCreateTicketApi',
];

