<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt Confirmation</title>
</head>
<body>
    <div>
    <h1>Payment Receipt Confirmation</h1>
    <p>Dear {{ $details['associate_name'] }},</p>
    <p>Thank you for your payment. Below are the details of your payment:</p>
    <ul>
        <li><strong>Receipt Number:</strong> {{ $details['receipt_number'] }}</li>
        <li><strong>Received Amount:</strong> {{ $details['received_amount'] }}</li>
        <li><strong>Received Date:</strong> {{ $details['received_date'] }}</li>
        <li><strong>Received Method:</strong> {{ $details['received_method'] }}</li>
    </ul>
    <p>If you have any questions, feel free to contact us.</p>
    <p>Best regards,<br>Your Company</p>
    </div>
</body>
</html>