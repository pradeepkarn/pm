<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

class Orders_ctrl
{
    public $db;
    function __construct()
    {
        $this->db = (new DB_ctrl)->db;
    }
    public function import_orders($req = null)
    {
        if (isset($_POST['action'], $_FILES)) {
            $this->save_imported_orders($req = new stdClass);
            exit;
        }
        $context = (object) array(
            'page' => 'allorders/create.php',
            'data' => (object) array(
                'req' => obj($req),
                'is_active' => true
            )
        );
        $this->render_main($context);
    }
    public function create_order_manually($req = null)
    {
        if (isset($_POST['action'], $_FILES)) {
            $this->add_manual_order($req = new stdClass);
            exit;
        }
        $req = obj($req);
        // $order = $this->db->showOne("select * from manual_orders where id = $req->id");
        $context = (object) array(
            'page' => 'allorders/create-manually.php',
            'data' => (object) array(
                'req' => obj($req),
                'is_active' => true,
                // 'order_detail' => $order,
            )
        );
        $this->render_main($context);
    }
    public function edit_order($req = null)
    {
        if (isset($_POST['action'], $_FILES)) {
            $this->update_order($req = new stdClass);
            exit;
        }
        $req = obj($req);
        $order = $this->db->showOne("select * from manual_orders where id = $req->id");
        $context = (object) array(
            'page' => 'allorders/edit.php',
            'data' => (object) array(
                'req' => obj($req),
                'is_active' => true,
                'order_detail' => $order,
            )
        );
        $this->render_main($context);
    }
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
        if (isset($req->status)) {
            $res =  $this->order_list_by_delv_status(ord: "DESC", limit: $page_limit, active: 1, delv_sts: $req->status);
        } else {
            $res = $this->order_list(ord: "DESC", limit: $page_limit, active: 1);
        }
        if ($res) {
            $orders_list = [];
            foreach ($res as $d) {
                // myprint($d);
                $apidata = obj($d); // true parameter for associative array
                // $user_to_driver = $apidata->user_to_rest*1000 + $driver_to_rest;
                $dat = array(
                    'id' => $apidata->id,
                    'orderid' => $apidata->id,
                    'buyer' => $apidata->name,
                    'buyer_name' => $apidata->name,
                    "buyer_id" => $apidata->email,
                    "buyer_lat" => $apidata->lat,
                    "buyer_lon" => $apidata->lon,
                    "pickup_lat" => $apidata->pickup_lat,
                    "pickup_lon" => $apidata->pickup_lon
                );
                $d['api_data'] = $dat;
                $orders_list[] = $d;
            }
        } else {
            $orders_list = [];
        }
        // myprint($orders_list);

        $tu = $this->order_list_count($active = 1, $req->status ?? null)['total_orders'] ?? 0;
        if (isset($req->search)) {
            $res = $this->order_search_list(keyword: $req->search, ord: "DESC", limit: $page_limit, active: 1, delv_sts: $req->status);
            $orders_list = $this->format_orders($res);
        }
        if ($tu %  $data_limit == 0) {
            $tu = $tu / $data_limit;
        } else {
            $tu = floor($tu / $data_limit) + 1;
        }
        $context = (object) array(
            'page' => 'allorders/list.php',
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
    public function assigned_order_list($req = null)
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
        $res =  $this->new_assigned_order_list(ord: "DESC", limit: $page_limit, active: 1, is_assigned: $req->is_assigned);
        if ($res) {
            $orders_list = [];
            foreach ($res as $d) {
                // myprint($d);
                $apidata = obj($d); // true parameter for associative array
                // $user_to_driver = $apidata->user_to_rest*1000 + $driver_to_rest;
                $dat = array(
                    'id' => $apidata->id,
                    'orderid' => $apidata->id,
                    'buyer' => $apidata->name,
                    'buyer_name' => $apidata->name,
                    "buyer_id" => $apidata->email,
                    "buyer_lat" => $apidata->lat,
                    "buyer_lon" => $apidata->lon,
                    "pickup_lat" => $apidata->pickup_lat,
                    "pickup_lon" => $apidata->pickup_lon
                );
                $d['api_data'] = $dat;
                $orders_list[] = $d;
            }
        } else {
            $orders_list = [];
        }
        // myprint($orders_list);

        $tu = $this->assigned_order_list_count($active = 1, $is_assigned = $req->is_assigned)['total_orders'] ?? 0;
        if (isset($req->search)) {
            $res = $this->new_assigned_order_search_list(keyword: $req->search, ord: "DESC", limit: $page_limit, active: 1, is_assigned: $req->is_assigned);
            $orders_list = $this->format_orders($res);
            $tu = count($orders_list);
        }
        if ($tu %  $data_limit == 0) {
            $tu = $tu / $data_limit;
        } else {
            $tu = floor($tu / $data_limit) + 1;
        }
        $context = (object) array(
            'page' => 'allorders/new-list.php',
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
    function format_orders($res)
    {
        $orders_list = [];
        foreach ($res as $d) {
            // myprint($d);
            $apidata = obj($d); // true parameter for associative array
            // $user_to_driver = $apidata->user_to_rest*1000 + $driver_to_rest;
            $dat = array(
                'id' => $apidata->id,
                'orderid' => $apidata->id,
                'buyer' => $apidata->name,
                'buyer_name' => $apidata->name,
                "buyer_id" => $apidata->email,
                "buyer_lat" => $apidata->lat,
                "buyer_lon" => $apidata->lon,
                "pickup_lat" => $apidata->pickup_lat,
                "pickup_lon" => $apidata->pickup_lon
            );
            $d['api_data'] = $dat;
            $orders_list[] = $d;
        }
        return $orders_list;
    }
    function new_assigned_order_list($ord = "DESC", $limit = 5, $active = 1, $is_assigned = 1)
    {
        $db = new Dbobjects;
        // $db->tableName = "manual_orders";
        if ($is_assigned == 1) {
            return $db->show("select * from manual_orders where is_active='$active' and driver_id !='0' and delivery_status=0 order by id $ord limit $limit");
        } else {
            return $db->show("select * from manual_orders where is_active='$active' and driver_id ='0' and delivery_status=0 order by id $ord limit $limit");
        }
    }
    function order_search_list($keyword, $ord = "DESC", $limit = 5, $active = 1, $delv_sts = 1)
    {
        $db = new Dbobjects;

        $condition = "is_active = '$active' AND delivery_status = '$delv_sts'";
        $searchColumns = [
            'id',
            'created_at',
            'phone',
            'order_item',
            'quantity',
            'amount',
            'email',
            'name',
            'address',
            'pickup_address'
        ];

        $searchConditions = [];
        foreach ($searchColumns as $column) {
            $searchConditions[] = "$column LIKE '%$keyword%'";
        }

        $searchCondition = implode(" OR ", $searchConditions);

        $sql = "SELECT * FROM manual_orders WHERE $condition AND ($searchCondition) ORDER BY id $ord LIMIT $limit";

        return $db->show($sql);
    }
    function new_assigned_order_search_list($keyword, $ord = "DESC", $limit = 5, $active = 1, $is_assigned = 1)
    {
        $db = new Dbobjects;

        $condition = "is_active = '$active' AND delivery_status = 0 AND ";
        $condition .= ($is_assigned == 1) ? "driver_id != '0'" : "driver_id = '0'";

        $searchColumns = [
            'id',
            'created_at',
            'phone',
            'order_item',
            'quantity',
            'amount',
            'email',
            'name',
            'address',
            'pickup_address'
        ];

        $searchConditions = [];
        foreach ($searchColumns as $column) {
            $searchConditions[] = "$column LIKE '%$keyword%'";
        }

        $searchCondition = implode(" OR ", $searchConditions);

        $sql = "SELECT * FROM manual_orders WHERE $condition AND ($searchCondition) ORDER BY id $ord LIMIT $limit";

        return $db->show($sql);
    }

    function order_list_by_delv_status($ord = "DESC", $limit = 5, $active = 1, $delv_sts = 0)
    {
        $db = new Dbobjects;
        $db->tableName = "manual_orders";
        return $db->filter(assoc_arr: ['is_active' => $active, 'delivery_status' => $delv_sts], ord: $ord, limit: $limit);
    }
    function order_list($ord = "DESC", $limit = 5, $active = 1)
    {
        $db = new Dbobjects;
        $db->tableName = "manual_orders";
        return $db->filter(assoc_arr: ['is_active' => $active], ord: $ord, limit: $limit);
    }
    function assigned_order_list_count($active = 1, $is_assigned = 1)
    {
        $db = new Dbobjects;
        $db->tableName = "manual_orders";
        if ($is_assigned == 1) {
            return $db->showOne("select COUNT(id) as total_orders from manual_orders where is_active=$active and delivery_status='0' and driver_id!='0'");
        } else {
            return $db->showOne("select COUNT(id) as total_orders from manual_orders where is_active=$active and delivery_status='0' and driver_id='0'");
        }
    }
    function order_list_count($active = 1, $delv_sts = null)
    {
        $delv_str  = null;
        if ($delv_sts) {
            $delv_str = "and delivery_status= '$delv_sts'";
        }
        $db = new Dbobjects;
        $db->tableName = "manual_orders";
        return $db->showOne("select COUNT(id) as total_orders from manual_orders where is_active=$active $delv_str");
    }
    function save_imported_orders($req = null)
    {
        $req = (object) $_POST;
        $req->sheet = (object) $_FILES ?? null;
        $rules = [
            'action' => 'required|string',
            'sheet' => 'required|file',
        ];
        $pass = validateData(data: arr($req), rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn(return: true));
            exit;
        } else {
            $db = new Dbobjects;
            if (isset($req->sheet->sheet)) {
                $file = $req->sheet->sheet;
                // myprint($file);
                $spreadsheet = IOFactory::load($file['tmp_name']);

                $sheet = $spreadsheet->getActiveSheet();

                // Prepare the SQL statement
                $sql = "INSERT INTO manual_orders 
                (created_at, name, email, phone, address, lat, lon, pickup_address, pickup_lat, pickup_lon, order_item, quantity, amount, order_type, driver_id) 
                VALUES 
                (:created_at, :name, :email, :phone, :address, :lat, :lon, :pickup_address, :pickup_lat, :pickup_lon, :order_item, :quantity, :amount, :order_type, :driver_id)";
                // Loop through each row and insert data into the database
                $count = 0;
                for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
                    $created_at = $sheet->getCell('A' . $row)->getCalculatedValue();
                    // $created_at = trim($created_at)??time();
                    // $created_at = timestampToDatetime($created_at);
                    $name = $sheet->getCell('B' . $row)->getCalculatedValue();
                    $email = $sheet->getCell('C' . $row)->getCalculatedValue();
                    $phone = $sheet->getCell('D' . $row)->getCalculatedValue();
                    $address = $sheet->getCell('E' . $row)->getCalculatedValue();
                    $loc = $sheet->getCell('F' . $row)->getCalculatedValue();
                    $pkg = $sheet->getCell('G' . $row)->getCalculatedValue();
                    $qty = $sheet->getCell('H' . $row)->getCalculatedValue();
                    $amt = $sheet->getCell('I' . $row)->getCalculatedValue();
                    $order_type = $sheet->getCell('J' . $row)->getCalculatedValue();
                    $driver_id = $sheet->getCell('K' . $row)->getCalculatedValue();

                    $created_at = $this->convertToValidDateFormat($created_at);
                    $cord = $this->separateCoordinates($coordinates = $loc);
                    $old_sql = "select id from manual_orders where phone='$phone' and created_at = '$created_at'";
                    $exists = $db->showOne($old_sql);
                    if (!$exists) {
                        // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        //     msg_set("Valid Email not found in the row $row");
                        // }
                        if ($cord[0] == '' || $cord[1] == '') {
                            msg_set("Valid location not found in the row $row");
                        }
                        $params = [
                            ':created_at' => $created_at,
                            ':name' => $name,
                            ':email' => $email,
                            ':phone' => $phone,
                            ':address' => $address,
                            ':pickup_address' => WAREHOUSE['pickup_address'],
                            ':pickup_lat' => WAREHOUSE['pickup_lat'],
                            ':pickup_lon' => WAREHOUSE['pickup_lon'],
                            ':lat' => $cord[0],
                            ':lon' => $cord[1],
                            ':order_item' => $pkg,
                            ':quantity' => $qty,
                            ':amount' => floatval($amt),
                            ':order_type' => intval($order_type) == 1 ? 1 : 0,
                            ':driver_id' => intval($driver_id) ? intval($driver_id) : 0,
                        ];
                    } else {
                        msg_set("Duplicate entry found in the row $row");
                        // $count = -2;
                    }
                    try {
                        if (isset($params)) {
                            $reply = false;
                            $count += 1;
                            $stmt = $db->pdo->prepare($sql);
                            $reply = $stmt->execute($params);
                            // if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            //     $reply = $stmt->execute($params);
                            // }else{
                            //     msg_set("Invalid email $row");
                            // }
                        }
                    } catch (PDOException $e) {
                        msg_set("Database import error");
                    }
                }
                msg_set("$count data imported");
                echo msg_ssn();
            }
        }
        exit;
    }
    function add_manual_order($req = null)
    {
        $req = (object) $_POST;
        $rules = [
            'name' => 'required|string',
            'phone' => 'required|numeric',
            'order_item' => 'required|string',
            'quantity' => 'required|numeric',
            'amount' => 'required|numeric',
            'created_at' => 'required|datetime',
            'action' => 'required|string',
            'address' => 'required|string',
            'pickup_address' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'pickup_lat' => 'required|numeric',
            'pickup_lon' => 'required|numeric',
        ];
        $pass = validateData(data: arr($req), rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn(return: true));
            exit;
        } else {
            $db = $this->db;
            $old_sql = "select id from manual_orders where created_at='$req->created_at' and phone='$req->phone';";
            $exists = $db->showOne($old_sql);
            if (!$exists) {
                try {
                    $params = [
                        ':created_at' => $req->created_at,
                        ':phone' => $req->phone,
                        ':order_item' => $req->order_item,
                        ':quantity' => $req->quantity,
                        ':amount' => $req->amount,
                        ':order_type' => $req->order_type ? 1 : 0,
                        ':email' => $req->email??null,
                        ':name' => $req->name,
                        ':address' => $req->address,
                        ':pickup_address' => $req->pickup_address,
                        ':lat' => $req->lat,
                        ':lon' => $req->lon,
                        ':pickup_lat' => $req->pickup_lat,
                        ':pickup_lon' => $req->pickup_lon,
                    ];

                    $sql = "INSERT INTO manual_orders 
                            (created_at, phone, order_item, quantity, amount, order_type, email, name, address, pickup_address, lat, lon, pickup_lat, pickup_lon)
                            VALUES 
                            (:created_at, :phone, :order_item, :quantity, :amount, :order_type, :email, :name, :address, :pickup_address, :lat, :lon, :pickup_lat, :pickup_lon)";

                    $stmt = $db->pdo->prepare($sql);

                    if ($stmt->execute($params)) {
                        msg_set("Order created");
                        echo js_alert(msg_ssn(return:true));
                        echo RELOAD;
                        return;
                    } else {
                        msg_set("data not inserted");
                    }
                } catch (PDOException $e) {
                    msg_set("Database import error");
                }
            } else {
                msg_set("This order is already in database");
            }
            echo js_alert(msg_ssn(return: true));
        }
        exit;
    }
    function update_order($req = null)
    {
        $req = (object) $_POST;
        $rules = [
            'action' => 'required|string',
            'id' => 'required|numeric',
            'address' => 'required|string',
            'pickup_address' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'pickup_lat' => 'required|numeric',
            'pickup_lon' => 'required|numeric',
        ];
        $pass = validateData(data: arr($req), rules: $rules);
        if (!$pass) {
            echo js_alert(msg_ssn(return: true));
            exit;
        } else {
            $db = $this->db;
            $old_sql = "select id from manual_orders where id='$req->id';";
            $exists = $db->showOne($old_sql);
            if ($exists) {
                try {
                    $params = [
                        ':id' => $req->id,
                        ':address' => $req->address,
                        ':name' => $req->name ?? null,
                        ':pickup_address' => $req->pickup_address,
                        ':lat' => $req->lat,
                        ':lon' => $req->lon,
                        ':pickup_lat' => $req->pickup_lat,
                        ':pickup_lon' => $req->pickup_lon,
                    ];
                    $sql = "UPDATE manual_orders SET 
                    address = :address ,
                    name = :name ,
                    pickup_address = :pickup_address ,
                    lat = :lat ,
                    lon = :lon ,
                    pickup_lat = :pickup_lat ,
                    pickup_lon = :pickup_lon 
                    WHERE id = :id";
                    $stmt = $db->pdo->prepare($sql);
                    if ($stmt->execute($params)) {
                        msg_set("data updated");
                    } else {
                        msg_set("data not updated");
                    }
                } catch (PDOException $e) {
                    msg_set("Database import error");
                }
            } else {
                msg_set("Object not found in database");
            }
            echo js_alert(msg_ssn(return: true));
        }
        exit;
    }
    function convertToValidDateFormat($inputDate)
    {
        // Remove extra spaces and trim the input
        $cleanedDate = trim(preg_replace('/\s+/', ' ', $inputDate));

        // Define possible date formats
        $dateFormats = [
            'm/d/y, h:i A',
            'm/d/y, h:iA',
            'm/d/y, h A',
            'm/d/y, hA',
        ];

        // Try to parse the date using each format
        foreach ($dateFormats as $format) {
            $dateTime = DateTime::createFromFormat($format, $cleanedDate);
            if ($dateTime !== false) {
                // Return the date in a specific format (adjust as needed)
                return $dateTime->format('Y-m-d H:i:s');
            }
        }

        // Return null if the date couldn't be parsed
        return null;
    }
    function separateCoordinates($coordinates)
    {
        // Trim leading and trailing spaces
        $coordinates = trim($coordinates);

        // Check if the string is empty or contains only a comma
        if (empty($coordinates) || $coordinates === ',') {
            return [null, null];
        }

        // Split the coordinates by comma
        $coordinatesArray = explode(',', $coordinates);

        // Trim each coordinate and remove empty values
        $coordinatesArray = array_map('trim', $coordinatesArray);
        $coordinatesArray = array_filter($coordinatesArray);

        // Check if there are at least two coordinates (latitude and longitude)
        if (count($coordinatesArray) < 2) {
            return [null, null];
        }

        // Return the separated coordinates
        return $coordinatesArray;
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
            $ruuning = $this->db->showOne("select * from manual_orders where driver_id = '{$driver_id}' and delivery_status IN (0,1)");
            if ($ruuning) {
                // msg_set("Driver already assigned");
                $this->db->insertData['driver_id'] = $driver_id;
            } else {
                $this->db->insertData['driver_id'] = $driver_id;
            }
        } else {
            $this->db->insertData['driver_id'] = "0";
        }
        $arready = $this->db->showOne("select id from manual_orders where id = '$d->orderid'");
        if ($arready) {
            $this->db->tableName = 'manual_orders';
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
        $this->db->tableName = 'manual_orders';
        $this->db->insertData['delivery_status'] = floatval($d->delivery_status ?? 0);
        $arready = $this->db->showOne("select id from manual_orders where id = '$d->orderid'");
        if ($arready) {
            $this->db->tableName = 'manual_orders';
            $this->db->pk($arready['id']);
            if ($this->db->update()) {
                msg_set('Order updated');
                $api['success'] = true;
                $api['data'] = [];
                $api['reload'] = true;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } else {
                msg_set('Order not updated');
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        }
    }
    // render function
    public function render_main($context = null)
    {
        import("apps/admin/layouts/admin-main.php", $context);
    }
    function delete_bulk()
    {
        $action = $_POST['action'] ?? null;
        $ids = $_POST['selected_ids'] ?? null;
        if ($action != null && $action == "delete_selected_items" && $ids != null) {
            $num = count($ids);
            if ($num == 0) {
                echo js_alert('Object not seleted');
                exit;
            };
            $idsString = implode(',', $ids);
            $db = new Dbobjects;
            $pdo = $db->conn;
            $pdo->beginTransaction();
            $sql = "DELETE FROM manual_orders WHERE id IN ($idsString)";
            try {
                $db->show($sql);
                $pdo->commit();
                echo js_alert("$num Selected item deleted");
                echo RELOAD;
                return true;
            } catch (PDOException $pd) {
                $pdo->rollBack();
                echo js_alert('Database quer error');
                return false;
            }
        } else {
            echo js_alert('Action not or items not selected');
            exit;
        }
    }
}
