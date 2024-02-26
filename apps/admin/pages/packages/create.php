<?php
$createData = $context;
$catlist = $context->cat_list;
?>

<form action="/<?php echo home . route('packageStoreAjax'); ?>" id="save-new-page-form">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title">Add package</h5>
                </div>
                <div class="col text-end my-3">
                    <a class="btn btn-dark" href="/<?php echo home . route('packageList'); ?>">Back</a>
                </div>
            </div>
            <div id="res"></div>
            <div class="row">
                <div class="col-md-8">
                    <h4>Title</h4>
                    <input type="text" name="title" class="form-control my-3" placeholder="Title">
                    <h6>Slug</h6>
                    <input type="text" name="slug" class="form-control my-3" placeholder="slug">
                    <h4>Category</h4>
                    <select name="parent_id" class="form-select my-3">
                        <option value="0">Uncategorised</option>
                        <?php foreach ($catlist as  $cv) {
                            $cv = obj($cv);
                        ?>
                            <option value="<?php echo $cv->id; ?>"><?php echo $cv->title; ?></option>
                        <?php } ?>
                        <?php ?>
                    </select>
                    <textarea class="tinymce-editor" name="content" id="mce_0" aria-hidden="true"></textarea>
                    <h4>Tags</h4>
                    <textarea class="form-control" name="meta_tags" aria-hidden="true"></textarea>
                    <h4>Meta description</h4>
                    <textarea class="form-control" name="meta_description" aria-hidden="true"></textarea>
                </div>
                <div class="col-md-4">
                    <h4>Banner</h4>
                    <input accept="image/*" id="image-input" type="file" name="banner" class="form-control my-3">
                    <img style="width:100%; max-height:300px; object-fit:contain;" id="banner" src="" alt="">
                    <div id="image-container"></div>
                    <button type="button" class="btn btn-secondary text-white mt-2" id="add-image">Images <i class="bi bi-plus"></i> </button>
                    <hr>
                    <h4>Price as commitment</h4>
                    <input type="number" scope="any" name="price" class="form-control my-3" placeholder="Price">

                    <h4>Service Hours</h4>
                    <input type="number" scope="any" name="service_hours" class="form-control my-3" placeholder="Hours">

                    <h4>Support Level</h4>
                    <select name="support_level" class="form-select">
                        <option value="1">7 days</option>
                        <option value="2">14 days</option>
                        <option value="3">24X7 days for 1 month</option>
                        <option value="4">24X7 days for 6 months</option>
                        <option value="5">24X7 days for 1 year</option>
                    </select>

                    <h4>Response Time</h4>
                    <select name="response_time" class="form-select">
                        <option value="1">Within 24hr</option>
                        <option value="2">With in 1 hour</option>
                        <option value="3">Always online</option>
                    </select>
                    <div class="d-grid">
                        <button id="save-page-btn" type="button" class="btn btn-primary my-3">Save</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>
<script>
    window.onload = () => {
        const imageInputPage = document.getElementById('image-input');
        const imagePage = document.getElementById('banner');

        imageInputPage.addEventListener('change', (event) => {
            const file = event.target.files[0];
            const fileReader = new FileReader();

            fileReader.onload = () => {
                imagePage.src = fileReader.result;
            };

            fileReader.readAsDataURL(file);
        });

        // for slug

        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');
        if (titleInput && slugInput) {
            titleInput.addEventListener('keyup', () => {
                const title = titleInput.value.trim();
                generateSlug(title, slugInput);
            });
        }
    }



    $(document).ready(function() {
        $('#add-image').on('click', function() {
            // Create a new image input field
            var newInput = '<input accept="image/*" type="file" name="moreimgs[]" class="form-control my-3">';
            $('#image-container').append(newInput);
        });
    });
</script>
<?php pkAjax_form("#save-page-btn", "#save-new-page-form", "#res"); ?>