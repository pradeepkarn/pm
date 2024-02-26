<?php
$createData = $context;
?>
<link rel="stylesheet" href="/<?php echo STATIC_URL; ?>/trumbowyg/dist/ui/trumbowyg.min.css">
<form action="/<?php echo home . route('productCatStoreAjax'); ?>" id="save-new-post-form">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title">Add category</h5>
                </div>
                <div class="col text-end my-3">
                    <a class="btn btn-dark" href="/<?php echo home . route('productCatList'); ?>">Back</a>
                </div>
            </div>
            <div id="res"></div>
            <div class="row">
                <div class="col-md-8">
                    <h4>Title</h4>
                    <input type="text" name="title" class="form-control my-3" placeholder="Title">
                    <textarea class="form-control" name="content" id="mce_0" aria-hidden="true"></textarea>
                </div>
                <div class="col-md-4">
                    <h4>Banner</h4>
                    <input accept="image/*" id="image-input" type="file" name="banner" class="form-control my-3">
                    <img style="width:100%; max-height:300px; object-fit:contain;" id="banner" src="" alt="">
                    <div class="d-grid">
                    <div id="res"></div>
                        <button id="save-post-btn" type="button" class="btn btn-primary my-3">Save</button>
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
<?php pkAjax_form("#save-post-btn", "#save-new-post-form", "#res"); ?>

<script src="/<?php echo STATIC_URL; ?>/trumbowyg/dist/trumbowyg.min.js"></script>
<script>
    $('.editor').trumbowyg({
        btns: [['formatting'],
        ['bold', 'italic', 'underline', 'strikethrough'],
        ['superscript', 'subscript'],
        ['link'],
        ['insertImage'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['horizontalRule'],
        ['removeformat']
        ]
    });
    $('#createCardBtn').click(() => {
        var text = $('#editor').trumbowyg('html');
        // console.log(text);
    });

</script>