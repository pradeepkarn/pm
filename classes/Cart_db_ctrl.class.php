<?php
class Cart_db_ctrl
{
    public $db; // Assuming you have a database class instance
    public $user_id; // Assuming you have a database class instance

    // Constructor to inject the database instance
    public function __construct()
    {
        $this->db = new Dbobjects;
        $this->user_id = USER ? USER['id'] : null;
    }
    function add_to_cart($id, $qty = 1)
    {
        if ($this->user_id) {

            $user_id = $this->user_id;
            $this->db->tableName = "carts";
            $arr['user_id'] = $user_id;
            $arr['is_active'] = 1;
            $cart = $this->db->findOne($arr);
            $cart = $cart ? json_decode($cart['cart'], true) : [];

            $found = false;
            foreach ($cart as &$item) {
                if ($item['id'] == $id) {
                    $item['qty'] += $qty;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $cart[] = ['id' => $id, 'qty' => $qty];
            }
        } else {
            $cart[] = ['id' => $id, 'qty' => $qty];
        }
        $user_id = $this->user_id;
        $this->db->tableName = "carts";
        $this->db->insertData['user_id'] = $user_id;
        $this->db->insertData['cart'] = json_encode($cart);
        $this->db->insertData['is_active'] = 1;
        if ($this->db->get(['user_id' => $user_id, 'is_active' => 1])) {
            $this->db->update();
        } else {
            $this->db->create();
        }
    }


    function remove_from_cart($id)
    {
        if ($this->user_id) {

            $user_id = $this->user_id;
            $this->db->tableName = "carts";
            $arr['user_id'] = $user_id;
            $arr['is_active'] = 1;
            $cart = $this->db->findOne($arr);
            $cart = $cart ? json_decode($cart['cart'], true) : [];

            foreach ($cart as $key => &$item) {
                if ($item['id'] == $id) {
                    if ($item['qty'] > 1) {
                        $item['qty'] -= 1;
                    } else {
                        unset($cart[$key]);
                    }
                    break;
                }
            }

            $this->db->tableName = "carts";
            $this->db->insertData['user_id'] = $user_id;
            $this->db->insertData['cart'] = json_encode($cart);
            $this->db->insertData['is_active'] = 1;

            if ($this->db->get(['user_id' => $user_id, 'is_active' => 1])) {
                $this->db->update();
            } else {
                $this->db->create();
            }
        }
    }
    function delete_from_cart($id)
    {
        if ($this->user_id) {

            $user_id = $this->user_id;
            $this->db->tableName = "carts";
            $arr['user_id'] = $user_id;
            $arr['is_active'] = 1;
            $cart = $this->db->findOne($arr);
            $cart = $cart ? json_decode($cart['cart'], true) : [];

            foreach ($cart as $key => &$item) {
                if ($item['id'] == $id) {
                    unset($cart[$key]);
                    break;
                }
            }

            $this->db->tableName = "carts";
            $this->db->insertData['user_id'] = $user_id;
            $this->db->insertData['cart'] = json_encode($cart);

            if ($this->db->get(['user_id' => $user_id, 'is_active' => 1])) {
                $this->db->update();
            } else {
                $this->db->create();
            }
        }
    }


    function count_cart_items()
    {
        $totalItems = 0;

        $this->db->tableName = "carts";
        $user_id = $this->user_id;
        $arr['user_id'] = $user_id;
        $arr['is_active'] = 1;
        $cart = $this->db->findOne($arr);
        $cart = $cart ? json_decode($cart['cart'], true) : [];

        if (isset($cart)) {
            foreach ($cart as $item) {
                $totalItems += $item['qty'];
            }
        }

        return $totalItems;
    }
    function count_cart()
    {
        $totalItems = 0;

        $this->db->tableName = "carts";
        $user_id = $this->user_id;
        $arr['user_id'] = $user_id;
        $arr['is_active'] = 1;
        $cart = $this->db->findOne($arr);
        $cart = $cart ? json_decode($cart['cart'], true) : [];
        if (isset($cart)) {
            $totalItems = count($cart);
        }

        return $totalItems;
    }
    function cart()
    {
        $productsInfo = [];
        $total_amt = 0;

        $user_id = $this->user_id;
        $this->db->tableName = "carts";
        $arr['user_id'] = $user_id;
        $arr['is_active'] = 1;
        $cartObj = $this->db->findOne($arr);
        $cart = $cartObj ? json_decode($cartObj['cart'], true) : [];
        if ($cartObj) {
            foreach ($cart as $item) {
                $productId = $item['id'];
                $cart_qty = $item['qty'];
                // Assuming you have a showOne method that fetches product info based on ID
                $productInfo = $this->db->showOne("SELECT * FROM content WHERE content_group='product' AND id = $productId");

                if ($productInfo) {
                    $price = $productInfo['price'];
                    $discount_amt = $productInfo['discount_amt'];
                    $price_wd = $price - $discount_amt;
                    $net_tax = round($price_wd * ($productInfo['tax'] / 100), 2);
                    $sale_price = $price_wd + $net_tax;
                    $unit = getUnitText($productInfo['qty_unit']);
                    $total_amt += round($sale_price * $cart_qty, 2);
                    $productsInfo[] = [
                        'id'    => $productId,
                        'title'  => $productInfo['title'],
                        'banner'  => img_or_null($productInfo['banner']),
                        'price' => round($price, 2),
                        'discount' => round($discount_amt, 2),
                        'vat_percent' => $productInfo['tax'],
                        'sale_price' => round($sale_price, 2),
                        'cart_qty' => $cart_qty,
                        'cart_amt' => round($sale_price * $cart_qty, 2),
                        'unit' => $unit,
                        'seller_id' => $productInfo['created_by']
                    ];
                }
            }
        }
        return array('items' => $productsInfo, 'total_amt' => round($total_amt, 2));
    }
    function place_order($req = null)
    {
        $lastId = 0;

        if ($this->count_cart() == 0) {
            msg_set("Cart is empty");
            return false;
        }
        $dbh = new Dbh;
        $pdo = $dbh->conn();
        $jsn = json_encode($this->cart());
        $amt = $this->cart()['total_amt'];
        if ($amt <= 0) {
            msg_set("Amount must be greater than zero");
            return false;
        }
        try {
            // Start the transaction
            $pdo->beginTransaction();

            $status = $req->status;
            $payment_method = $req->payment_method;
            $name = $req->name;
            $isd_code = $req->isd_code;
            $mobile = $req->mobile;
            $city = $req->city;
            $state = $req->state;
            $country = $req->country;
            $address = $req->address;
            $req->lat = $req->lat?$req->lat:0;
            $req->lon = $req->lon?$req->lon:0;
            if (!is_numeric($req->lat) || !is_numeric($req->lon)) {
                msg_set("Latitude and Longitude must be numeric value");
                return false;
            }
            // Prepare and execute the SQL query to insert payment data
            $params = [
                ':status' => $status,
                ':payment_method' => $payment_method,
                ':amount' => $amt,
                ':jsn' => $jsn,
                ':user_id' => $this->user_id,
                ':unique_id' => uniqid('ord'),
                ':timestamp' => gmdate('Y-m-d H:i:s'), // GMT time
                ':name' => $name,
                ':isd_code' => $isd_code,
                ':mobile' => $mobile,
                ':city' => $city,
                ':locality' => $address,
                ':state' => $state,
                ':country' => $country,
                ':zipcode' => isset($req->address) ? $req->address : null,
                ':lat' => $req->lat,
                ':lon' => $req->lon
            ];
            $sql = "INSERT INTO payment 
                    (status,payment_method,amount, jsn, user_id, unique_id, timestamp, name, isd_code, mobile, city, locality, state, country, zipcode, lat, lon) VALUES 
                    (:status,:payment_method,:amount, :jsn, :user_id, :unique_id, :timestamp, :name, :isd_code, :mobile, :city, :locality, :state, :country, :zipcode, :lat, :lon)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $lastId = $pdo->lastInsertId();
            $cartsql = "update carts set is_active = :is_active where user_id = :user_id and is_active = 1";
            $cartparams = [
                ':user_id' => $this->user_id,
                ':is_active' => 0,
            ];
            $stmt = $pdo->prepare($cartsql);
            if ($stmt->execute($cartparams)) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
            msg_set("Payment initiated");
            return ["order_id" => $lastId];
        } catch (PDOException $e) {
            $pdo->rollBack();
            msg_set("Something went wrong");
            return false;
        }
    }
    function get_my_orders($delivery_status = null, $order_id = null)
    {
        // Initialize the SQL query
        $sql = "SELECT * FROM payment WHERE user_id = '$this->user_id'";
        // Check if $order_id is not null
        if ($order_id !== null) {
            $sql .= " AND id = '$order_id'";
        }
        // Check if $delivery_status is not null
        if ($delivery_status !== null) {
            $sql .= " AND delivery_status = '$delivery_status' ORDER BY id desc";
        }
        if ($order_id) {
            $ord = $this->db->showOne($sql);
            return $this->format_order($ord);
        }

        $ordrs = $this->db->show($sql);
        $allord = array_map(function ($ord) {
            return $this->format_order($ord);
        }, $ordrs);

        return $allord;
    }
    function get_all_orders($delivery_status = null, $order_id = null)
    {
        // Initialize the SQL query
        $sql = "SELECT * FROM payment WHERE 1";
        // Check if $order_id is not null
        if ($order_id !== null) {
            $sql .= " AND id = '$order_id'";
        }
        // Check if $delivery_status is not null
        if ($delivery_status !== null) {
            $sql .= " AND delivery_status = '$delivery_status' ORDER BY id desc";
        }
        if ($order_id) {
            $ord = $this->db->showOne($sql);
            return $this->format_order($ord);
        }

        $ordrs = $this->db->show($sql);
        $allord = array_map(function ($ord) {
            return $this->format_order($ord);
        }, $ordrs);

        return obj(["orders" => $allord, "ordCount" => count($allord)]);
    }
    //sellers
    // function get_orders_by_seller_id($seller_id)
    // {
    //     if ($seller_id) {
    //         $ordrs = $this->db->show("SELECT id, unique_id, zipcode FROM payment WHERE JSON_EXTRACT(jsn, '$.items[0].seller_id') = '$seller_id';");
    //         $allord = array_map(function ($ord) {
    //             return $this->format_order($ord);
    //         }, $ordrs);
    //         return $allord;
    //     }else{
    //         return null;
    //     }
    // }
    function filter_seller_items_from_orders($seller_id, $delivery_status = null, $order_id = null)
    {
        if ($seller_id == null) {
            return null;
        }
        $sql = "SELECT payment.*, JSON_EXTRACT(jsn, '$.items') AS seller_items FROM payment WHERE 1 ";
        // Check if $order_id is not null
        if ($order_id !== null) {
            $sql .= " AND id = '$order_id'";
        }
        // Check if $delivery_status is not null
        if ($delivery_status !== null) {
            $sql .= " AND delivery_status = '$delivery_status'";
        }
        $sql .= "AND JSON_EXTRACT(jsn, '$.items[0].seller_id') = '$seller_id' ORDER BY id desc;";
        if ($order_id) {
            $ord = $this->db->showOne($sql);
            return $this->format_seller_order($ord, $seller_id);
        }
        $ordrs = $this->db->show($sql);
        $allord = array_map(function ($ord) use ($seller_id) {
            return $this->format_seller_order($ord, $seller_id);
        }, $ordrs);
        return obj(["orders" => $allord, "ordCount" => count($allord)]);
    }
    function format_order($ord)
    {
        $ord['jsn'] = json_decode($ord['jsn']);
        $ord['zipcode'] = strval($ord['zipcode']);
        return $ord;
    }
    function format_seller_order($ord, $seller_id)
    {
        $ord['seller_items'] = json_decode($ord['seller_items']);

        $allord = array_filter(array_map(function ($s_item) use ($seller_id) {
            if ($s_item->seller_id == $seller_id) {
                return $s_item;
            } else {
                return null;
            }
        }, $ord['seller_items']));

        $ord['seller_items'] = $allord;
        $ord['zipcode'] = strval($ord['zipcode']);
        return $this->format_order($ord);
    }
    function request_return_items(int $customer_id, string $unique_id, array $pid_list, string $info)
    {

        $dbh = new Dbh;
        $pdo = $dbh->conn();

        try {
            $pdo->beginTransaction();
            $find_order_sql = "SELECT * FROM payment WHERE unique_id = :unique_id AND delivery_status = 4";
            $stmt = $pdo->prepare($find_order_sql);
            $stmt->execute([":unique_id" => $unique_id]);
            $order = $stmt->fetch();
            if ($order) {
                $items = json_decode($order['jsn'], true)['items'] ?? [];
                foreach ($pid_list as $key => $item_id) {
                    $item = $this->findItemById($items, $item_id);
                    if ($item) {
                        $seller_id = $item['seller_id'];
                        $item_jsn = json_encode($item);
                        $created_at = gmdate('Y-m-d H:i:s');
                        $checksql = "SELECT * FROM `return_items` WHERE unique_id = :unique_id AND item_id = :item_id";
                        $params = array(
                            ':unique_id' => $unique_id,
                            ':item_id' => $item_id
                        );
                        $stmt = $pdo->prepare($checksql);
                        $stmt->execute($params);
                        $old = $stmt->fetch();
                        if (!$old) {
                            $insertsql = "INSERT INTO `return_items` 
              (`unique_id`, `seller_id`, `customer_id`, `item_id`, `item_jsn`, `info`, `is_approved`, `created_at`) 
              VALUES (:unique_id, :seller_id, :customer_id, :item_id, :item_jsn, :info, '0', :created_at)";

                            $params = array(
                                ':unique_id' => $unique_id,
                                ':seller_id' => $seller_id,
                                ':customer_id' => $customer_id,
                                ':item_id' => $item_id,
                                ':item_jsn' => $item_jsn,
                                ':info' => $info,
                                ':created_at' => $created_at
                            );

                            $stmt = $pdo->prepare($insertsql);
                            if ($stmt->execute($params)) {
                                msg_set("Request success for item: $item_id");
                            }
                        } else {
                            msg_set("Already in request item: $item_id");
                        }
                    }
                }
                $oldbyuinqueidsql = "SELECT * FROM `return_items` WHERE unique_id = :unique_id";
                $params = array(
                    ':unique_id' => $unique_id
                );
                $stmt = $pdo->prepare($oldbyuinqueidsql);
                $stmt->execute($params);
                $allOld = $stmt->fetchAll();
                $pdo->commit();
                if ($allOld) {
                    return array_map(function ($d) {
                        $d['item_jsn'] = json_decode($d['item_jsn']);
                        return $d;
                    }, $allOld);
                } else {
                    return null;
                }
            } else {
                msg_set("Currently this order is not eligible for return");
                return null;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            msg_set("Something went wrong");
            return null;
        }
    }
    function order_return_history_by_user($userid, $unique_id = null)
    {
        $sql = "SELECT * FROM `return_items` WHERE customer_id = '$userid' ORDER BY id DESC LIMIT 10";
        if ($unique_id) {
            $sql = "SELECT * FROM `return_items` WHERE customer_id = '$userid' AND unique_id = '$unique_id' ORDER BY id DESC";
        }
        $allOld = $this->db->show($sql);
        if ($allOld) {
            return array_map(function ($d) {
                $d['item_jsn'] = json_decode($d['item_jsn']);
                return $d;
            }, $allOld);
        } else {
            return null;
        }
    }
    function findItemById(array $items, $itemId)
    {
        foreach ($items as $item) {
            if ($item['id'] == $itemId) {
                return $item;
            }
        }
        return null;
    }
}
