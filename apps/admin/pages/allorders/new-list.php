<?php
$fl = $context->orders_list;
$tp = $context->total_orders;
$cp = $context->current_page;
$active = $context->is_active;

// $ug =  explode("/", REQUEST_URI);
// $ug = $ug[3];
// $req = new stdClass;
// $req->fg = $ug;
// myprint($fl);
$req = $context->req;
?>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body mt-3">
                    <form action="" class="p-1" style="background-color: lightgray;">
                        <div class="row my-3">
                            <div class="col">
                                <input value="<?php echo isset($_GET['search']) ? $_GET['search'] : null; ?>" type="search" class="form-control" name="search" placeholder="Search...">
                            </div>
                            <div class="col-2 text-end">
                                <div class="d-grid">
                                <button type="submit" class="btn btn-primary ">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <form action="<?php echo BASEURI . route('ordersDeleteBulkAJax'); ?>" id="delete-bulk-form">
                        <div id="deletebulkres"></div>
                        <div class="row">
                            <div class="col-md-4">
                                <select name="action" class="form-select" id="">
                                    <option value="">Action</option>
                                    <option value="delete_selected_items">Delete selected (Parmanently)</option>
                                </select>
                            </div>
                            <div class="col-md-4">

                                <button type="submit" id="delete-bulk-btn" class="btn btn-danger">Done</button>

                            </div>
                        </div>
                    </form>
                    <?php
                    ajaxActive("#upload-info");
                    pkAjax_form("#delete-bulk-btn", "#delete-bulk-form", "#deletebulkres");
                    ?>
                    <!-- Table with stripped rows -->
                    <div class="table-responsive">


                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <input type="checkbox" id="selct_all_ids"> Select
                                    </th>
                                    <th class="text-center">Edit</th>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Delivery Status</th>
                                    <th scope="col">Buyer</th>
                                    <!-- <th scope="col">Driver to Pickup</th> -->
                                    <!-- <th scope="col">Buyer To Pickup</th> -->
                                    <th class="text-center">Assign driver Set price or both</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $db = new Dbobjects;


                                foreach ($fl as $key => $pv) :
                                    $pv = obj($pv['api_data']);

                                    $btnid = uniqid("btn{$pv->orderid}");
                                    $formid = uniqid("form{$pv->orderid}");

                                    $ord = $db->showOne("select add_on_price, driver_id, delivery_status from manual_orders where id = '$pv->orderid'");
                                    $add_on_price = $ord['add_on_price'];
                                    $driver_id = $ord['driver_id'];
                                    $delivery_status = $ord['delivery_status'];
                                    $driver = $db->showOne("select id, email, lat, lon from pk_user where is_active=1 and user_group = 'driver' and id ='{$driver_id}'");
                                    $drivers = $db->show("select id, email, lat, lon from pk_user where is_active=1 and user_group = 'driver'");
                                    $driver_to_rest = null;
                                    if ($driver) {
                                        $driver_to_rest = calculateDistance($startLat = $pv->pickup_lat, $startLon = $pv->pickup_lon, $endLat = $driver['lat'], $endLon = $driver['lon']);
                                        $driver_to_rest = $driver_to_rest ? round($driver_to_rest / 1000, 3) : null;
                                    }

                                ?>
                                    <tr>
                                        <th>
                                            <input type="checkbox" name="selected_obj_id" value="<?php echo $pv->id; ?>">
                                        </th>
                                        <th>
                                            <a href="<?php echo BASEURI . route('allOrdersEdit', ['id' => $pv->orderid]); ?>">
                                                <div class="bi bi-pen"></div>
                                            </a>
                                        </th>
                                        <th><?php echo $pv->orderid; ?></th>

                                        <th>
                                            <?php //echo getStatusText($statusCode = $delivery_status); 
                                            ?>
                                            <form id="<?php echo $formid; ?>status" method="post" action="<?php echo BASEURI . route('allOrdersUpdateStatus'); ?>">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div>
                                                        <select id="<?php echo $btnid; ?>select" class="<?php echo $delivery_status == '2' ? 'bg-success text-white' : null;
                                                                                                        echo $delivery_status == '1' ? 'bg-info text-dark' : null;
                                                                                                        echo $delivery_status == '3' ? 'bg-danger text-dark' : null; ?> form-control" name="delivery_status">
                                                            <option value="0">--Status--</option>
                                                            <?php
                                                            foreach (STATUS_CODES as $key => $sts) {
                                                                $drv = obj($drv);
                                                            ?>
                                                                <option <?php echo $key == $delivery_status ? 'selected' : null; ?> value="<?php echo $key; ?>"><?php echo $sts; ?></option>
                                                            <?php }
                                                            ?>
                                                        </select>
                                                        <input type="hidden" name="orderid" value="<?php echo $pv->orderid; ?>">
                                                    </div>
                                                </div>
                                            </form>
                                            <?php send_to_server(button: "#{$btnid}select", data: "#{$formid}status", event: "change"); ?>
                                        </th>
                                        <!-- <th><?php //echo $pv->driver_assigned ? $pv->driver : 'NA'; 
                                                    ?></th> -->
                                        <th><?php echo $pv->buyer_name; ?></th>
                                        <!-- <th><?php //echo $driver_to_rest ?? "NA"; 
                                                    ?></th> -->
                                        <!-- <th><?php //echo $pv->user_to_rest . " " . $pv->distance_unit; 
                                                    ?></th> -->

                                        <th>
                                            <form id="<?php echo $formid; ?>" method="post" action="<?php echo BASEURI . route('allOrdersUpdateAddOnPrice'); ?>">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div>
                                                        <select class="<?php echo $driver_id ? 'bg-success text-white' : null; ?> form-control" name="driver_id">
                                                            <option value="0">--Driver--</option>
                                                            <?php
                                                            foreach ($drivers as $drv) {
                                                                $drv = obj($drv);
                                                            ?>
                                                                <option <?php echo $driver_id == $drv->id ? 'selected' : null; ?> value="<?php echo $drv->id; ?>"><?php echo $drv->email; ?></option>
                                                            <?php }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <input type="hidden" name="orderid" value="<?php echo $pv->orderid; ?>">
                                                        <input type="text" name="add_on_price" value="<?php echo $add_on_price; ?>" class="form-control">
                                                    </div>
                                                    <div>
                                                        <button type="button" id="<?php echo $btnid; ?>" class="btn btn-sm btn-primary">Set</button>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php send_to_server("#$btnid", "#$formid"); ?>
                                        </th>

                                    </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- End Table with stripped rows -->
                </div>
                <div class="custom-pagination">
                    <?php
                    $pg = isset($_GET['page']) ? $_GET['page'] : 1;
                    $tu = $tp; // Total pages
                    $current_page = $cp; // Assuming first page is the current page
                    if ($active == true) {
                        $link =  route('allOrdersAssignedList', ['is_assigned' => $req->is_assigned ?? 0]);
                    } else {
                        $link =  route('allOrdersAssignedList', ['is_assigned' => $req->is_assigned ?? 0]);
                    }
                    // Calculate start and end page numbers to display
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($start_page + 4, $tu);

                    // Show first page button if not on the first page
                    if ($current_page > 1) {
                        echo '<a class="first-button" href="/' . home . $link . '?page=1">&laquo;</a>';
                    }

                    // Show ellipsis if there are more pages before the start page
                    if ($start_page > 1) {
                        echo '<span>...</span>';
                    }

                    // Display page links within the range
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active_class = ($pg == $i) ? "active" : null;
                        echo '<a class="' . $active_class . '" href="/' . home . $link . '?page=' . $i . '">' . $i . '</a>';
                    }

                    // Show ellipsis if there are more pages after the end page
                    if ($end_page < $tu) {
                        echo '<span>...</span>';
                    }

                    // Show last page button if not on the last page
                    if ($current_page < $tu) {
                        echo '<a class="last-button" href="/' . home . $link . '?page=' . $tu . '">&raquo;</a>';
                    }
                    ?>
                </div>

            </div>

        </div>
    </div>
</section>

<script>
    const selectAllCheckbox = document.getElementById('selct_all_ids');
    const individualCheckboxes = document.querySelectorAll('input[name="selected_obj_id"]');
    const deleteBulkForm = document.getElementById('delete-bulk-form');

    selectAllCheckbox.addEventListener('change', function() {
        individualCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
            updateFormInputs(checkbox);
        });
    });

    individualCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateFormInputs(checkbox);
            selectAllCheckbox.checked = Array.from(individualCheckboxes).every(checkbox => checkbox.checked);
        });
    });

    function updateFormInputs(checkbox) {
        if (checkbox.checked) {
            appendInput(deleteBulkForm, 'selected_ids[]', checkbox.value);
        } else {
            removeInput(deleteBulkForm, 'selected_ids[]', checkbox.value);
        }
    }

    function appendInput(form, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    function removeInput(form, name, value) {
        const inputToRemove = form.querySelector(`input[name="${name}"][value="${value}"]`);
        if (inputToRemove) {
            form.removeChild(inputToRemove);
        }
    }

    deleteBulkForm.addEventListener('submit', function(event) {
        individualCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                removeInput(deleteBulkForm, 'selected_ids[]', checkbox.value);
            }
        });
    });
</script>