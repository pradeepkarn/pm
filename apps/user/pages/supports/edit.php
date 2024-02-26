<?php
$support_detail = $context->support_detail;
$cd = obj($support_detail);
$msg_list = obj($context->messages);
// myprint($msg_list);
$cg =  explode("/", REQUEST_URI);
$cg = $cg[3];
$req = new stdClass;
$req->cg = $cg;
?>

<form action="" id="update-support-form">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title">Add user</h5>
                </div>
                <div class="col text-end my-3">
                    <a class="btn btn-dark" href="/<?php echo home . route('userSupportList', ['cg' => $req->cg]); ?>">Back</a>
                </div>
            </div>
            <div id="res"></div>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Assign user</h4>
                            <?php
                            import("apps/admin/helpers/modules/user-search.php", obj([
                                'mid' => uniqid('module'),
                                'input_name' => 'assigned_user',
                                'selected' => $cd->assigned_user
                            ]));
                            ?>
                        </div>
                        <div class="col-md-12">
                            <h4>Subject</h4>
                            <textarea class="form-control" name="subject" aria-hidden="true"><?php echo $cd->subject; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <h4>File</h4>
                    <input accept="image/*,application/pdf" id="file-input" type="file" name="attachment" class="form-control my-3">
                    <div class="d-grid">
                        <input type="hidden" name="action" value="update-ticket">
                        <input type="hidden" name="id" value="<?php echo $cd->id; ?>">
                        <button id="update-support-btn" type="button" class="btn btn-primary my-3">Update</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>
<?php pkAjax_form("#update-support-btn", "#update-support-form", "#res"); ?>
<div class="row">
    <?php
    import("apps/admin/helpers/modules/support-messages.php", obj([
        'mid' => uniqid('module'),
        'msg_list' => $msg_list,
        'support_id' => $context->req->id,
    ]));
    ?>
</div>
