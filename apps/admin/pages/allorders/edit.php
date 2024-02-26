<?php
$order_detail = $context->order_detail;
$fd = obj($order_detail);
// $fg =  explode("/", REQUEST_URI);
// $fg = $fg[3];
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
                            <h5 class="card-title">Update</h5>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <h4>Email</h4>
                            <input readonly type="email" value="<?php echo $fd->email; ?>" name="email" class="form-control my-3" placeholder="Email">
                        </div>

                        <div class="col-md-4">
                            <h4>Mobile</h4>
                            <input id="mobile" type="number" name="mobile" value="<?php echo $fd->phone; ?>" class="form-control my-3" placeholder="mobile">
                        </div>
                        <div class="col-md-12">
                            <h4>Name</h4>
                            <input type="text" name="name" value="<?php echo $fd->name; ?>" class="form-control my-3" placeholder="First name">
                        </div>

                    </div>
                    <div class="d-grid">

                        <input type="hidden" name="action" value="update_order">
                        <input type="hidden" name="id" value="<?php echo $fd->id; ?>">
                        <button id="update-fuel-btn" type="button" class="btn btn-primary my-3">Update</button>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 text-end my-3">
                            <a class="btn btn-dark" href="/<?php echo home . route('allOrdersList',['status'=>$fd->delivery_status]); ?>">Back</a>
                        </div>

                        <div class="col-md-12">

                            <b>Customer address</b>
                            <textarea name="address" class="form-control my-3" placeholder="Customer address"><?php echo $fd->address; ?></textarea>
                            <div class="col-md-12">
                                <div id="location-form">
                                </div>
                                <div id="map"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <b>Customer Latitude</b>
                            <input id="custLat" type="text" name="lat" value="<?php echo $fd->lat; ?>" class="form-control my-3">
                        </div>
                        <div class="col-md-6">
                            <b>Customer Longitude</b>
                            <input id="custLon" type="text" name="lon" value="<?php echo $fd->lon; ?>" class="form-control my-3">
                        </div>

                        <div class="col-md-12">
                            <b>Pickup address</b>
                            <textarea name="pickup_address" class="form-control my-3" placeholder="Pickup address"><?php echo $fd->pickup_address; ?></textarea>
                            <div class="col-md-12">
                                <div id="location-form-shop">
                                </div>
                                <div id="map-shop"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <b>Pickup Latitude</b>
                            <input id="pickLat" type="text" name="pickup_lat" value="<?php echo $fd->pickup_lat; ?>" class="form-control my-3">
                        </div>
                        <div class="col-md-6">
                            <b>Pickup Longitude</b>
                            <input id="pickLon" type="text" name="pickup_lon" value="<?php echo $fd->pickup_lon; ?>" class="form-control my-3">
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