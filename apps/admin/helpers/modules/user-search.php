<div class="my-2">
    <div id="container<?php echo $ctx->mid; ?>">
    <input type="hidden" name="<?php echo $ctx->input_name; ?>" value="<?php echo $ctx->selected??1; ?>">
</div>
    <select id="search<?php echo $ctx->mid; ?>" class="form-select">
        <?php
         $plobj = new Dbobjects;
         $isdcodes = $plobj->show("select id,username,email,first_name,last_name from pk_user");
         $selected = $ctx->selected??1;
         $phpdata = [];
        foreach ($isdcodes as $key => $cd) {
            $cd = obj($cd);
            $phpdata[] = $cd;
        ?>
            <option <?php echo "$selected" == "$cd->id" ? "selected" : null; ?> value="<?php echo $cd->id; ?>"><?php echo $cd->username; ?></option>
        <?php } 
        $jsnData = json_encode($phpdata);
        ?>
    </select>
    <script>
        $(document).ready(function() {
            let prevIsdCode = "<?php echo "{$selected}"; ?>";
            if (prevIsdCode) {
                $('#search<?php echo $ctx->mid; ?>').val(prevIsdCode);
            }
            // Initialize Select2 on the ISD code search input
            $('#search<?php echo $ctx->mid; ?>').select2({
                placeholder: 'Search country',
                data: <?php echo $jsnData; ?>
            });
            // Handle search functionality
            $('#search<?php echo $ctx->mid; ?>').on('change', function() {
                let selectedCode = $(this).val();
                // Add the selected value to the form data
                $("#container<?php echo $ctx->mid; ?>").html('<input type="hidden" name="<?php echo $ctx->input_name; ?>" value="' + selectedCode + '">');
            });
        });
    </script>
</div>