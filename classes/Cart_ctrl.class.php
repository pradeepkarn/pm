<?php
class Cart_ctrl
{
    public $db; // Assuming you have a database class instance

    // Constructor to inject the database instance
    public function __construct()
    {
        $this->db = new Dbobjects;
    }

    function add_to_cart($id, $qty = 1)
    {
        if (isset($_SESSION['cart'])) {
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $id) {
                    $item['qty'] += $qty;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $_SESSION['cart'][] = ['id' => $id, 'qty' => $qty];
            }
        } else {
            $_SESSION['cart'][] = ['id' => $id, 'qty' => $qty];
        }
    }

    function remove_from_cart($id)
    {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => &$item) {
                if ($item['id'] == $id) {
                    if ($item['qty'] > 1) {
                        $item['qty'] -= 1;
                    } else {
                        unset($_SESSION['cart'][$key]);
                    }
                    break;
                }
            }

            if (empty($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
        }
    }
    function delete_from_cart($id)
    {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => &$item) {
                if ($item['id'] == $id) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }
            if (empty($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
        }
    }
    function count_cart_items()
    {
        $totalItems = 0;

        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $totalItems += $item['qty'];
            }
        }

        return $totalItems;
    }
    function count_cart()
    {
        $totalItems = 0;

        if (isset($_SESSION['cart'])) {
            $totalItems = count($_SESSION['cart']);
        }

        return $totalItems;
    }
    function cart()
    {
        $productsInfo = [];
        $total_amt = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
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
                        'seller_id'=>$productInfo['created_by']
                    ];
                }
            }
        }
        return array('items' => $productsInfo, 'total_amt' => round($total_amt, 2));
    }
    function save_cart_in_db()
    {
        if (authenticate()) {
            if (isset($_SESSION['cart'])) {
                $user_id = USER['id'];
                $session_items = $_SESSION['cart'];
                $this->db->tableName = "carts";
                $arr['user_id'] = $user_id;
                $arr['is_active'] = 1;
                $cart = $this->db->findOne($arr);
                $db_items = $cart ? json_decode($cart['cart'], true) : [];
                // Create an associative array with 'id' as the key for easy lookup
                $db_items_by_id = [];
                foreach ($db_items as $db_item) {
                    $db_items_by_id[$db_item['id']] = $db_item;
                }

                // Merge the arrays by adding quantities for items with the same ID
                foreach ($session_items as $session_item) {
                    $id = $session_item['id'];

                    if (isset($db_items_by_id[$id])) {
                        // If the ID exists in the database items, update the quantity
                        $db_items_by_id[$id]['qty'] += $session_item['qty'];
                    } else {
                        // If the ID doesn't exist in the database items, add it
                        $db_items_by_id[$id] = $session_item;
                    }
                }

                // Convert the merged array back to JSON
                $mergedData = json_encode(array_values($db_items_by_id));

                $this->db->insertData['cart'] = $mergedData;
                $this->db->insertData['user_id'] = $user_id;
                // myprint($session_items);
                // myprint($db_items);
                // myprint($mergedData);

                if ($this->db->get(['user_id' => $user_id, 'is_active' => 1])) {
                    $this->db->update();
                } else {
                    $this->db->create();
                }
                unset($_SESSION['cart']);
            }
        }
    }
    function save_cart_in_db_via_api($user_id = null)
    {
        if ($user_id !=null && isset($_SESSION['cart'])) {
            $session_items = $_SESSION['cart'];
            $this->db->tableName = "carts";
            $arr['user_id'] = $user_id;
            $arr['is_active'] = 1;
            $cart = $this->db->findOne($arr);
            $db_items = $cart ? json_decode($cart['cart'], true) : [];
            // Create an associative array with 'id' as the key for easy lookup
            $db_items_by_id = [];
            foreach ($db_items as $db_item) {
                $db_items_by_id[$db_item['id']] = $db_item;
            }

            // Merge the arrays by adding quantities for items with the same ID
            foreach ($session_items as $session_item) {
                $id = $session_item['id'];

                if (isset($db_items_by_id[$id])) {
                    // If the ID exists in the database items, update the quantity
                    $db_items_by_id[$id]['qty'] += $session_item['qty'];
                } else {
                    // If the ID doesn't exist in the database items, add it
                    $db_items_by_id[$id] = $session_item;
                }
            }

            // Convert the merged array back to JSON
            $mergedData = json_encode(array_values($db_items_by_id));

            $this->db->insertData['cart'] = $mergedData;
            $this->db->insertData['user_id'] = $user_id;
            if ($this->db->get(['user_id' => $user_id, 'is_active' => 1])) {
                $this->db->update();
            } else {
                $this->db->create();
            }
            unset($_SESSION['cart']);
        }
    }
}
