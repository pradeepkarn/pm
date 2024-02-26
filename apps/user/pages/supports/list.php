<?php
$cl = $context->support_list;
$tc = $context->total_support;
$cp = $context->current_page;
$active = $context->is_active;

$cg =  explode("/", REQUEST_URI);
$cg = $cg[3];
$req = new stdClass;
$req->cg = $cg;
?>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col my-3">
                            <h5 class="card-title">All users</h5>
                            <nav class="nav">
                                <a class="nav-link <?php echo $active ? "btn btn-sm btn-primary text-white" : ""; ?>" href="/<?php echo home . route('userSupportList', ['cg' => $req->cg]); ?>">Active List</a>
                                <a class="nav-link <?php echo $active ? "" : "btn btn-sm btn-danger text-white"; ?>" href="/<?php echo home . route('userSupportTrashList', ['cg' => $req->cg]); ?>">Trash List</a>
                            </nav>

                        </div>
                        <div class="col my-3">
                            <form action="">
                                <div class="row">
                                    <div class="col-8">
                                        <input value="<?php echo isset($_GET['search']) ? $_GET['search'] : null; ?>" type="search" class="form-control" name="search" placeholder="Search...">
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-primary ">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col text-end my-3">
                            <a class="btn btn-dark" href="/<?php echo home . route('userSupportCreate', ['cg' => $req->cg]); ?>">Create New Ticket</a>
                        </div>
                    </div>

                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Active</th>
                                <th scope="col">Move to</th>
                                <th scope="col">Edit</th>
                                <th scope="col">Ticket ID</th>
                                <th scope="col">Subject</th>

                                <th scope="col">Date</th>
                                <?php
                                if ($active == true) { ?>
                                    <!-- <th scope="col">Edit</th> -->
                                <?php    }
                                ?>
                                <th scope="col">Action</th>
                                <?php
                                if ($active == false) { ?>
                                    <th scope="col">Restore</th>
                                <?php    }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cl as $key => $pv) :
                                $pv = obj($pv);
                                if ($pv->is_active == true) {
                                    $move_to_text = "Trash";
                                    $move_to_link = route('userSupportTrash', ['id' => $pv->id, 'cg' => $req->cg]);
                                } else {
                                    $move_to_link = route('userSupportDelete', ['id' => $pv->id, 'cg' => $req->cg]);
                                    $move_to_text = "Delete";
                                    $restore_text = "Restore";
                                    $restore_link = route('userSupportRestore', ['id' => $pv->id, 'cg' => $req->cg]);
                                }
                            ?>

                                <tr>
                                    <th scope="row"><?php echo $pv->id; ?></th>
                                    <td>
                                        <?php echo $pv->is_approved ? 'Approved' : 'Pending'; ?>
                                    </td>
                                    <?php if ($pv->content_group == 'open') : ?>
                                        <td>
                                            <button data-support-id="<?php echo $pv->id; ?>" class="closed-btn btn btn-sm <?php echo $pv->content_group == 'closed' ? 'btn-primary' : 'btn-basic'; ?>">
                                                <?php echo $pv->content_group == 'closed' ? 'Reopen' : 'closed'; ?>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="<?php echo BASEURI . route('userSupportEdit', ['cg' => $req->cg, 'id' => $pv->id]); ?>">
                                            <div class="bi bi-pen"></div>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $pv->unique_id; ?></a>
                                    </td>
                                    <td><?php echo $pv->subject; ?></td>


                                    <td><?php echo $pv->created_at; ?></td>
                                    <?php
                                    if ($active == true) { ?>
                                        <!-- <td>
                                            <a class="btn-primary btn btn-sm" href="/<?php //echo home . route('userSupportEdit', ['id' => $pv->id, 'cg' => $req->cg]); 
                                                                                        ?>">Edit</a>
                                        </td> -->
                                    <?php    }
                                    ?>

                                    <td>
                                        <a class="btn-danger btn btn-sm" href="/<?php echo home . $move_to_link; ?>"><?php echo $move_to_text; ?></a>
                                    </td>
                                    <?php
                                    if ($active == false) { ?>
                                        <td>
                                            <a class="btn-success btn btn-sm" href="/<?php echo home . $restore_link; ?>"><?php echo $restore_text; ?></a>
                                        </td>
                                    <?php    }
                                    ?>

                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- End Table with stripped rows -->
                    <!-- Pagination -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">

                            <?php
                            $tc = $tc;
                            $current_page = $cp; // Assuming first page is the current page
                            if ($active == true) {
                                $link =  route('userSupportList', ['cg' => $req->cg]);
                            } else {
                                $link =  route('userSupportTrashList', ['cg' => $req->cg]);
                            }
                            // Show first two pages
                            for ($i = 1; $i <= $tc; $i++) {
                            ?>
                                <li class="page-item"><a class="page-link" href="/<?php echo home . $link . "?page=$i"; ?>"><?php echo $i; ?></a></li>
                            <?php
                            } ?>




                        </ul>
                    </nav>

                    <!-- Pagination -->
                </div>

            </div>

        </div>
    </div>
</section>

<script>
    window.onload = () => {
        const approveBtns = document.querySelectorAll(".approve-btn");
        for (const elm of approveBtns) {
            elm.addEventListener('click', () => {
                const support_id = elm.getAttribute('data-support-id');
                sendData({
                        support_id: support_id,
                        action: 'is_approved'
                    },
                    `/<?php echo home . route('userSupportToggleMarked', ['cg' => $req->cg]) ?>`,
                    (err, response) => {
                        if (err) {
                            // console.error('Error:', err);
                        } else {

                            res = JSON.parse(response)
                            // console.log('Response:', res);
                            if (res.msg == "success") {
                                // console.log('Response:', response);
                                alert(res.data)
                                location.reload();
                            } else {
                                alert(res.msg);
                            }
                            // do something with the response data
                        }
                    });
            });

        }
        const closedBtns = document.querySelectorAll(".closed-btn");
        for (const elm of closedBtns) {
            elm.addEventListener('click', () => {
                const support_id = elm.getAttribute('data-support-id');
                sendData({
                        support_id: support_id,
                        action: 'content_group'
                    },
                    `/<?php echo home . route('userSupportToggleClosed', ['cg' => $req->cg]) ?>`,
                    (err, response) => {
                        if (err) {
                            // console.error('Error:', err);
                        } else {

                            res = JSON.parse(response)
                            // console.log('Response:', res);
                            if (res.msg == "success") {
                                // console.log('Response:', response);
                                alert(res.data)
                                location.reload();
                            } else {
                                alert(res.msg);
                            }
                            // do something with the response data
                        }
                    });
            });

        }
    }
</script>