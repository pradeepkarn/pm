<?php
$createData = $context;
$uri =  explode("/", REQUEST_URI);

?>

<form action="" id="register-new-fuel-form">
    <div class="card">
        <div class="card-body">
            <div style="overflow-y: scroll; max-height:200px;" id="res"></div>
            <div class="row">
                <div class="col-md-8 my-2">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Import Orders</h5>
                        </div>
                        <div class="col text-end">
                            <a class="btn btn-dark" href="/<?php echo home . route('allOrdersAssignedList', ['is_assigned'=>'0']); ?>">All Orders</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="file" name="sheet" class="form-control">
                            <input type="hidden" name="action" value="sheet_upload">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button id="register-fuel-btn" type="button" class="btn btn-primary my-3">Import</button>
                    </div>
                </div>
                <div class="col-md-4 my-2">
                    <a class="btn btn-success" href="/<?php echo MEDIA_URL; ?>/site/sample.xlsx" download="">Download Sample File</a>
                </div>
            </div>

        </div>
    </div>

</form>
<?php pkAjax_form("#register-fuel-btn", "#register-new-fuel-form", "#res"); ?>

<!-- Helpers -->

<?php import("apps/admin/helpers/js/user-search.js.php"); ?>



<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // Get the value of the 'sponserid' cookie
        var sponserid = getCookie('sponserid');

        // Set a default value if 'sponserid' is not found in the cookie
        if (!sponserid) {
            sponserid = 'pool'; // Replace 'defaultSponserid' with your desired default value
        }

        // Find all elements with class '.maxbutton-1'
        var afl1 = document.querySelector('#afl-1');
        var afl2 = document.querySelector('#afl-2');
        var afl3 = document.querySelector('#afl-3');

        // Set the href attribute based on the 'sponserid' value for each element
        if(afl1) {
            afl1.href = 'https://member.viamo.world/signup/?sponserid=' + encodeURIComponent(sponserid);
        }
        if(afl2) {
            afl1.href = 'https://member.viamo.world/signup/?sponserid=' + encodeURIComponent(sponserid);
        }
        if(afl3) {
            afl1.href = 'https://member.viamo.world/signup/?sponserid=' + encodeURIComponent(sponserid);
        }

        secondAnchors.forEach(function(anchor) {
            afl1.href = 'https://member.viamo.world/signup/?sponserid=' + encodeURIComponent(sponserid);
        });
    });
</script>
