<?php
if (isset($_POST['submit'])) {
    date_default_timezone_set('Africa/Nairobi');

    $consumerKey = 'ynlRWTPIgVyUfSs8nRQbo3zhiUzMwM4oRAvkYLGZ54fLTJdC';
    $consumerSecret = 'GtbZ1Hwimx8NA6aBrBVm09OgZ9en2RVOGixXoDYnyJHX4451ZnXbHyRldcRYd2t9';
    $BusinessShortCode = '174379';
    $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    $PartyA = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $AccountReference = '2255';
    $TransactionDesc = 'Test Payment';
    $Amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
    $Timestamp = date('YmdHis');
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
    $headers = ['Content-Type:application/json; charset=utf8'];

    $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $CallBackURL = 'https://your-callback-url.com/callback_url.php';

    // Get access token
    $curl = curl_init($access_token_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        die('Error: ' . curl_error($curl));
    }

    if ($status != 200) {
        echo 'Failed to get access token. Status: ' . $status . '<br>';
        echo 'Response: ' . $result; // Print the response for debugging
        exit();
    }

    $result = json_decode($result);
    $access_token = $result->access_token;
    curl_close($curl);

    // Initiate STK push
    $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $initiate_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);

    $curl_post_data = [
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $Password,
        'Timestamp' => $Timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $Amount,
        'PartyA' => $PartyA,
        'PartyB' => $BusinessShortCode,
        'PhoneNumber' => $PartyA,
        'CallBackURL' => $CallBackURL,
        'AccountReference' => $AccountReference,
        'TransactionDesc' => $TransactionDesc
    ];

    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    $curl_response = curl_exec($curl);

    if (curl_errno($curl)) {
        die('Error: ' . curl_error($curl));
    }

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($status != 200) {
        echo 'Failed to initiate STK push. Status: ' . $status . '<br>';
        echo 'Response: ' . $curl_response; // Print the response for debugging
        exit();
    }

    curl_close($curl);

    // Redirect to a confirmation page
    header('Location: LASTPAGE.php');
    exit();
}
?>
