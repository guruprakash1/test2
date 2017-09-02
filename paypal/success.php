<?php
include '../includes/conn.php';

//Get payment information from PayPal
$item_number = $_GET['item_number']; 
$txn_id = $_GET['tx'];
$payment_gross = $_GET['amt'];
$currency_code = $_GET['cc'];
$payment_status = $_GET['st'];

//Get product price from database
$productResult = "SELECT price FROM products WHERE id = ".$item_number;
$result = mysqli_query($conn, $productResult);
$productRow = mysqli_fetch_assoc($result);
$productPrice = $productRow['price'];

if(!empty($txn_id) && $payment_gross == $productPrice){
    //Check if payment data exists with the same TXN ID.
    $prevPaymentResult = mysqli_query($conn,"SELECT payment_id FROM payments WHERE txn_id = '".$txn_id."'");

    if(mysqli_num_rows($prevPaymentResult) > 0){
        $paymentRow = mysqli_fetch_assoc($prevPaymentResult);
        $last_insert_id = $paymentRow['payment_id'];
    }else{
        //Insert tansaction data into the database
        $insert = mysqli_query($conn,"INSERT INTO payments(item_number,txn_id,payment_gross,currency_code,payment_status) VALUES('".$item_number."','".$txn_id."','".$payment_gross."','".$currency_code."','".$payment_status."')");
        $last_insert_id = 1;
    }
?>
    <h1>Your payment has been successful.</h1>
    <h1>Your Payment ID - <?php echo $last_insert_id; ?></h1>
<?php }else{ ?>
    <h1>Your payment has failed.</h1>
<?php } ?>