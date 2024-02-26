
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <style>
        /* Custom CSS to style the table */
        body {
            background-image: url('login-reg-bg.png'); /* Replace with your gaming-themed background image */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }
        th {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black background for table headers */
            color: white; /* Text color for table headers */
        }
        tbody tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background for alternate rows */
            color: white; /* Text color for alternate rows */
        }
        tbody tr {
            background-color: rgba(0, 0, 0, 0.7); /* Hover effect for rows on desktop */
        }

     /* Apply a dark, semi-transparent background to the entire table */
.table-dark-transparent {
    background-color: rgba(0, 0, 0, 0.2); /* Semi-transparent black background */
    color: white; /* Text color on the background */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Box shadow for the table */
    border-radius: 20px;

}

/* Style the table headers with a dark background */
.table-dark-transparent th {
    background-color: rgba(0, 0, 0, 0.2); /* Semi-transparent black background for table headers */
    color: white; /* Text color for table headers */
    border-radius: 20px;
}

/* Style both <tr> and <td> with dark backgrounds */
.table-dark-transparent tbody tr,
.table-dark-transparent tbody td {
    background-color: rgba(0, 0, 0, 0.2); /* Semi-transparent black background for both <tr> and <td> */
    color: white; /* Text color for both <tr> and <td> */
}

/* Hover effect for rows on desktop */
.table-dark-transparent tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.2);
}


        .instructions-container {
            position: relative;
        }
        .instructions {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black background for instructions */
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            z-index: 1;
            transition: display 0.3s;
            color: white; /* Text color for instructions */
        }
        .instructions-container:hover .instructions {
            display: block;
        }


        /* Responsive styles */
        @media (max-width: 768px) {
            tbody tr:hover .instructions-container .instructions {
                display: block; /* Show instructions on mobile when row is tapped */
            }
            tbody tr:hover {
                background-color: transparent; /* Remove hover effect on mobile */
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Transaction Details</h1>
        <?php
       msg_ssn();
        $transaction = $context->transaction;
        $explanation = $context->explanation;

        echo '<p><strong>Explanation:</strong> ' . $explanation . '</p>';

        echo '<div class="card mt-3">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">Transaction ID: ' . $transaction->TransactionToken . '</h5>';
        echo '<p><strong>Transaction Created Date:</strong> ' . $transaction->TransactionCreatedDate . '</p>';
        echo '<p><strong>Transaction Status:</strong> ' . $transaction->TransactionStatus . '</p>';
        echo '<p><strong>Transaction Amount:</strong> ' . $transaction->TransactionAmount . ' ' . $transaction->TransactionCurrency . '</p>';
        // Add more transaction details as needed
        echo '</div>';
        echo '</div>';
        ?>
    </div>

    <!-- Include Bootstrap JS and jQuery (optional) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.0.0-beta2/js/bootstrap.min.js"></script>
</body>
</html>
