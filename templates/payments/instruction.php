
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

</head>
<body>
    <div class="container mt-4">
        <h1>Payment Options</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Payment Name</th>
                    <th>Logo</th>
                    <th>Cellular Prefix</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                msg_ssn();
                $data = $context->mobileoptions;
                $transRef = $context->TransRef;
                foreach ($data as $item) {
                    $item = arr($item);
                    $item['instructions'] = str_replace("{PAYMENTTOKEN}",$transRef,$item['instructions']);
                    echo '<tr>';
                    echo '<td>' . $item['country'] . '</td>';
                    echo '<td>' . $item['paymentname'] . '</td>';
                    echo '<td><img src="' . $item['logo'] . '" alt="' . $item['paymentname'] . ' Logo" height="50"></td>';
                    echo '<td>' . $item['celluarprefix'] . '</td>';
                    echo '<td>' . $item['amount'] . '</td>';
                    echo '<td>' . $item['currency'] . '</td>';
                    echo '<td>' . lineBreakBySemicolon($item['instructions']) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <div class="row">
            <div class="col-md-4 mx-auto text-center my-4">
                <div id="res"></div>
                <button id="verify_my_status" type="button" class="btn btn-primary">Click here after mobile transaction</button>
                <input type="hidden" name="trans_ref" class="verify" value="<?php echo $transRef; ?>">
                <?php pkAjax("#verify_my_status",route('payNowVerify'),".verify","#res"); ?>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
