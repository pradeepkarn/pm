<?php
$createData = $context;
$ug =  explode("/",REQUEST_URI);
$ug = $ug[3];
$req = new stdClass;
$req->ug = $ug;
?>

<form action="" id="create-support-form">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title">Create Ticket</h5>
                </div>
                <div class="col text-end my-3">
                    <a class="btn btn-dark" href="/<?php echo home . route('supportList',['cg'=>$req->ug]); ?>">Back</a>
                </div>
            </div>
            <div id="res"></div>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                        <h4>Assign user</h4>
                            <?php 
                            import("apps/admin/helpers/modules/user-search.php",obj([
                                'mid'=>uniqid('module'),
                                'input_name'=>'assigned_user',
                                'selected'=>1
                            ]));
                            ?>
                        </div>
                        <div class="col-md-12">
                            <h4>Subject</h4>
                            <textarea class="form-control" name="subject" aria-hidden="true"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <h4>File</h4>
                    <input accept="image/*,application/pdf" id="file-input" type="file" name="attachment" class="form-control my-3">
                    <div class="d-grid">
                        <input type="hidden" name="action" value="create-ticket">
                        <button id="create-support-btn" type="button" class="btn btn-primary my-3">Create</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>

<?php pkAjax_form("#create-support-btn", "#create-support-form", "#res"); ?>