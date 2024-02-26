<?php
$createData = $context;
$ug =  explode("/",REQUEST_URI);
$ug = $ug[3];
// $req = new stdClass;
$req= $context->req;
?>

<form action="/<?php echo home.route('userStoreAjax',['ug'=>$req->ug]); ?>" id="register-new-user-form">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title">Add <?php echo $req->ug; ?></h5>
                </div>
                <div class="col text-end my-3">
                    <a class="btn btn-dark" href="/<?php echo home . route('userList',['ug'=>$req->ug]); ?>">Back</a>
                </div>
            </div>
            <div id="res"></div>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>Email</h4>
                            <input type="email" name="email" class="form-control my-3" placeholder="Email">
                        </div>
                        <div class="col-md-4">
                            <h4>Username</h4>
                            <input type="text" name="username" class="form-control my-3" placeholder="username">
                        </div>
                        <div class="col-md-6">
                            <h4>First name</h4>
                            <input type="text" name="first_name" class="form-control my-3" placeholder="First name">
                        </div>
                        <div class="col-md-6">
                            <h4>Lats name</h4>
                            <input type="text" name="last_name" class="form-control my-3" placeholder="Last name">
                        </div>
                        <?php if ($req->ug=='driver'): ?>
                        
                            <div class="col-md-6 my-2">
                                <label for="">National ID No.</label>
                                <input type="text" name="nid_no" class="form-control">
                            </div>
                            <div class="col-md-6 my-2">
                                <label for="">National ID DOC (PDF)</label>
                                <input accept="application/pdf" type="file" name="nid_doc" class="form-control">
                            </div>
                        
                        
                            <div class="col-md-6 my-2">
                                <label for="">Vehicle No.</label>
                                <input type="text" name="vhcl_no" class="form-control">
                            </div>
                            <div class="col-md-6 my-2">
                                <label for="">Vehicle Doc (PDF)</label>
                                <input accept="application/pdf" type="file" name="vhcl_doc" class="form-control">
                            </div>

                            <div class="col-md-6 my-2">
                                <label for="">DL No.</label>
                                <input type="text" name="dl_no" class="form-control">
                            </div>
                            <div class="col-md-6 my-2">
                                <label for="">DL DOC (PDF)</label>
                                <input accept="application/pdf" type="file" name="dl_doc" class="form-control">
                            </div>
                        
                        <?php endif; ?>
                        <div class="col-md-12">
                            <h4>Bio</h4>
                            <textarea class="form-control" name="bio" aria-hidden="true"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <h4>Profile Image</h4>
                    <input accept="image/*" id="image-input" type="file" name="image" class="form-control my-3">
                    <img style="width:100%; max-height:300px; object-fit:contain;" id="banner" src="" alt="">
                    <h4>Password</h4>
                    <input type="text" name="password" class="form-control my-3" placeholder="Password">
                    
                    <div class="d-grid">
                        <button id="register-user-btn" type="button" class="btn btn-primary my-3">Save</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>
<script>
    const imageInputPost = document.getElementById('image-input');
    const imagePost = document.getElementById('banner');

    imageInputPost.addEventListener('change', (event) => {
        const file = event.target.files[0];
        const fileReader = new FileReader();

        fileReader.onload = () => {
            imagePost.src = fileReader.result;
        };

        fileReader.readAsDataURL(file);
    });
</script>
<?php pkAjax_form("#register-user-btn", "#register-new-user-form", "#res"); ?>