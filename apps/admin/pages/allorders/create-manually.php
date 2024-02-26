<?php


$req = $context->req;
// $driver = isset($context->driver) ? obj($context->driver) : null;
?>


<div class="card">
    <div class="card-body">

        <div id="res"></div>
        <form action="" id="update-new-fuel-form">
            <div class="row">
                <div class="col-md-8">

                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Create new order</h5>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Order Item</h4>
                            <input type="text" name="order_item" class="form-control my-3" placeholder="Order Item Name">
                        </div>
                        <div class="col-md-4">
                            <h4>Created At</h4>
                            <input type="datetime-local" name="created_at" class="form-control my-3" placeholder="Order datetime">
                        </div>
                        <div class="col-md-4">
                            <h4>Quantity</h4>
                            <input id="qty" type="number" scope="any" name="quantity" class="form-control my-3" placeholder="Quantity in numeric value">
                        </div>
                        <div class="col-md-4">
                            <h4>Amount</h4>
                            <input id="amt" type="number" scope="any" name="amount" class="form-control my-3" placeholder="Final amount (quantity X price)">
                        </div>
                        <div class="col-md-4">
                            <h4>Order Type</h4>
                            <select id="ot" name="order_type" class="form-select my-3">
                                <option value="0">COD</option>
                                <option value="1">PREPAID</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <h4>Email</h4>
                            <input type="email" name="email" class="form-control my-3" placeholder="Email">
                        </div>

                        <div class="col-md-4">
                            <h4>Mobile</h4>
                            <input id="mobile" type="number" name="phone" class="form-control my-3" placeholder="phone">
                        </div>
                        <div class="col-md-8">
                            <h4>Name</h4>
                            <input type="text" name="name" class="form-control my-3" placeholder="Full name">
                        </div>

                    </div>
                    <div class="d-grid">

                        <input type="hidden" name="action" value="update_order">
                        <button id="update-fuel-btn" type="button" class="btn btn-primary my-3">Create</button>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 text-end my-3">
                        <a class="btn btn-dark" href="/<?php echo home . route('allOrdersAssignedList',['is_assigned'=>'0']); ?>">Back</a>
                        </div>

                        <div class="col-md-12">

                            <b>Customer address</b>
                            <textarea name="address" class="form-control my-3" placeholder="Customer address"></textarea>
                            <div class="col-md-12">
                                <div id="location-form">
                                </div>
                                <div id="map"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <b>Customer Latitude</b>
                            <input id="custLat" type="text" name="lat" class="form-control my-3">
                        </div>
                        <div class="col-md-6">
                            <b>Customer Longitude</b>
                            <input id="custLon" type="text" name="lon" class="form-control my-3">
                        </div>

                        <div class="col-md-12">
                            <b>Pickup address</b>
                            <textarea name="pickup_address" class="form-control my-3" placeholder="Pickup address"></textarea>
                            <div class="col-md-12">
                                <div id="location-form-shop">
                                </div>
                                <div id="map-shop"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <b>Pickup Latitude</b>
                            <input id="pickLat" type="text" name="pickup_lat" class="form-control my-3">
                        </div>
                        <div class="col-md-6">
                            <b>Pickup Longitude</b>
                            <input id="pickLon" type="text" name="pickup_lon" class="form-control my-3">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



<?php pkAjax_form("#update-fuel-btn", "#update-new-fuel-form", "#res"); ?>
<?php import("apps/admin/pages/allorders/mapbox.js.php");
?>
<script>
    initializeMap('map-shop', '<?php echo MAPBOX_ACCESS_TOKEN; ?>', 'location-form-shop', 'pickLat', 'pickLon', 'Seach coordinates');
    initializeMap('map', '<?php echo MAPBOX_ACCESS_TOKEN; ?>', 'location-form', 'custLat', 'custLon', 'Seach coordinates');
</script>