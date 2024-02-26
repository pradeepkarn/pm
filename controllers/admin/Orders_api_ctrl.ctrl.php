<?php
class Orders_api_ctrl
{
    public $db;
    function __construct()
    {
        $this->db = (new DB_ctrl)->db;
    }

    // List fuels
    public function list($req = null)
    {
        $req = obj($req);
        $current_page = 0;
        $data_limit = DB_ROW_LIMIT;
        $page_limit = "0,$data_limit";
        $cp = 0;
        if (isset($req->page) && intval($req->page)) {
            $cp = $req->page;
            $current_page = (abs($req->page) - 1) * $data_limit;
            $page_limit = "$current_page,$data_limit";
        }
        $res = $this->order_list(order_group: $req->fg, ord: "DESC", limit: 10000, active: 1);
        if ($res['success'] == true) {
            $orders_list = [];
            foreach ($res['data'] as $d) {
                // myprint($d);
                $apidata = obj($d); // true parameter for associative array
                // $user_to_driver = $apidata->user_to_rest*1000 + $driver_to_rest;
                $dat = array(
                    'id' => $apidata->id,
                    'orderid' => $apidata->orderid,
                    'buyer' => $apidata->buyer,
                    'buyer_name' => $apidata->buyer_name,
                    "buyer_id" => $apidata->buyer_id,
                    "buyer_lat" => $apidata->buyer_lat,
                    "buyer_lon" => $apidata->buyer_lon,
                    "rest_lat" => $apidata->rest_lat,
                    "rest_lon" => $apidata->rest_lon,
                    "distance_unit" => $apidata->distance_unit,
                    "user_to_rest" => $apidata->user_to_rest
                );
                $d['api_data'] = $dat;
                $orders_list[] = $d;
            }
        } else {
            $orders_list = [];
        }
        // myprint($orders_list);
        $tu = count($orders_list);
        if ($tu %  $data_limit == 0) {
            $tu = $tu / $data_limit;
        } else {
            $tu = floor($tu / $data_limit) + 1;
        }
        // if (isset($req->search)) {
        //     $order_list = $this->order_search_list(order_group: $req->fg, keyword: $req->search, ord: "DESC", limit: $page_limit, active: 1);
        // } else {
        //     $order_list = $this->order_list(order_group: $req->fg, ord: "DESC", limit: $page_limit, active: 1);
        // }
        // print_r($orders_list);
        $context = (object) array(
            'page' => 'orders/list.php',
            'data' => (object) array(
                'req' => obj($req),
                'orders_list' => $orders_list,
                'total_orders' => $tu,
                'current_page' => $cp,
                'is_active' => true
            )
        );
        $this->render_main($context);
    }

    // User list
    public function order_list($order_group = "petrol", $ord = "DESC", $limit = 5, $active = 1)
    {
        /*
        // testing
        $response = '{
        "success": true,
        "data": [
        {
        "id": 40,
        "driver_assigned": true,
        "orderid": "64f2fa99b0068",
        "driver_id": 128,
        "driver": "sumit",
        "buyer_id": 110,
        "buyer": "mail2pkarn",
        "buyer_lat": "26.19573",
        "buyer_lon": "86.01837",
        "rest_lat": "26.152548",
        "rest_lon": "85.894543",
        "driver_lat": "26.156999",
        "driver_lon": "85.899506",
        "user_to_rest": 16.612,
        "driver_to_user": 17.937,
        "distance_unit": "km"
        },
        {
        "id": 41,
        "driver_assigned": false,
        "orderid": "64f30965912a0",
        "driver_id": 0,
        "driver": null,
        "buyer_id": 110,
        "buyer": "mail2pkarn",
        "buyer_lat": "26.193144",
        "buyer_lon": "85.734601",
        "rest_lat": "26.152548",
        "rest_lon": "85.894543",
        "driver_lat": null,
        "driver_lon": null,
        "user_to_rest": 23.006,
        "driver_to_user": 0,
        "distance_unit": "km"
        }
        ],
        "msg": "Data found\n"
        }';
        */

        // testing end
        $curl = curl_init();
        $RESTAURANT_API_KEY = RESTAURANT_API_KEY;

        curl_setopt_array($curl, array(
            CURLOPT_URL => REST_API_ENDPOINT . '/api/v1/get/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "api_key: $RESTAURANT_API_KEY",
                'Content-Type: application/json',
                'Cookie: PHPSESSID=h2otnfm4qconqaidl8c2ro7ghl; lang=en'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        if (isset($response)) {
            $res = json_decode($response, true);
            try {
                $this->format_and_save($data = $res['data']);
            } catch (PDOException $th) {
            }
            // try {
            //     $data = $res['data'];
            //     array_map(function ($d) {
            //         $d = obj($d);
            //         $this->db->tableName = 'orders';
            //         $this->db->insertData['unique_id'] = $d->orderid;
            //         $arready = $this->db->showOne("select id from orders where unique_id = '$d->orderid'");
            //         if ($arready) {
            //             $this->db->tableName = 'orders';
            //             $single = $this->db->pk($arready['id']);
            //             $d->add_on_price = $single['add_on_price'];
            //             $this->db->insertData['jsn'] = json_encode($d);
            //             $this->db->update();
            //         } else {
            //             $d->add_on_price = 0;
            //             $this->db->create();
            //         }
            //         $this->db->insertData = null;
            //     }, $data);
            // } catch (PDOException $th) {
            // }
        }
        return json_decode($response, true);
    }
    function format_and_save(array $data)
    {
        array_map(function ($d) {
            $d = obj($d);
            $this->db->tableName = 'orders';
            $this->db->insertData['unique_id'] = $d->orderid;
            $arready = $this->db->showOne("select id from orders where unique_id = '$d->orderid'");
            if ($arready) {
                $this->db->tableName = 'orders';
                $single = $this->db->pk($arready['id']);
                $d->add_on_price = $single['add_on_price'];
                $this->db->insertData['jsn'] = json_encode($d);
                if ($this->db->update()) {
                    msg_set("order Updated");
                } else {
                    msg_set('Not updated');
                }
            } else {
                $d->add_on_price = 0;
                $this->db->insertData['jsn'] = json_encode($d);
                if ($this->db->create()) {
                    msg_set("order created");
                } else {
                    msg_set('Not created');
                }
            }
            $this->db->insertData = null;
        }, $data);
    }
    function update_on_purchase_event_from_client($req = null)
    {
        header("Content-Type: application/json");
        $headers = getallheaders();
        $api_key = $headers['api_key'] ?? null;
        $req = obj($req);
        $data  = json_decode(file_get_contents('php://input'));
        $rules = [
            'success' => 'required|bool'
        ];
        $pass = validateData(data: arr($data), rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }

        if (hash_equals(RESTAURANT_API_KEY, $api_key)) {
            if (isset($data->data)) {
                try {
                    $res = json_decode(json_encode($data->data), true);
                    $this->format_and_save($data = [$res]);
                    msg_set("data saved");
                    $api['success'] = true;
                    $api['data'] = [];
                    $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                    echo json_encode($api);
                    exit;
                } catch (PDOException $th) {
                    msg_set("data saving error");
                    $api['success'] = false;
                    $api['data'] = null;
                    $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                    echo json_encode($api);
                    exit;
                }
            }
        } else {
            msg_set("Invalid sitekey");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function update_addon_price($req = null)
    {
        $rules = [
            'orderid' => 'required|string',
        ];

        $pass = validateData(data: $_POST, rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $d = obj($_POST);
        $driver_id = $d->driver_id ?? "0";
        $this->db->tableName = 'orders';
        $this->db->insertData['add_on_price'] = floatval($d->add_on_price ?? 0);


        if ($driver_id != "0") {
            $ruuning = $this->db->showOne("select * from orders where driver_id = '{$driver_id}' and delivery_status IN (0,1)");
            if ($ruuning) {
                msg_set("Driver already assigned");
            } else {
                $this->db->insertData['driver_id'] = $driver_id;
            }
        } else {
            $this->db->insertData['driver_id'] = "0";
        }
        $arready = $this->db->showOne("select id from orders where unique_id = '$d->orderid'");
        if ($arready) {
            $this->db->tableName = 'orders';
            $this->db->pk($arready['id']);
            if ($this->db->update()) {
                msg_set('Orders updated');
                $api['success'] = true;
                $api['data'] = [];
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } else {
                msg_set('Orders not updated');
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        } else {
            msg_set('Orders not found');
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function update_status($req = null)
    {
        $rules = [
            'orderid' => 'required|string',
        ];
        $pass = validateData(data: $_POST, rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $d = obj($_POST);
        $this->db->tableName = 'orders';
        $this->db->insertData['delivery_status'] = floatval($d->delivery_status ?? 0);
        $arready = $this->db->showOne("select id from orders where unique_id = '$d->orderid'");
        if ($arready) {
            $this->db->tableName = 'orders';
            $this->db->pk($arready['id']);
            if ($this->db->update()) {
                msg_set('Orders updated');
                $api['success'] = true;
                $api['data'] = [];
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } else {
                msg_set('Orders not updated');
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        }
    }
    function order_search_list($order_group = "petrol", $keyword = "", $ord = "DESC", $limit = 5, $active = 1)
    {
        return [];
    }
    // User detail

    // render function
    public function render_main($context = null)
    {
        import("apps/admin/layouts/admin-main.php", $context);
    }
}
