<?php 
session_start();
require_once("vendor/autoload.php");
if(file_exists(__DIR__ . "/../.env")) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . "/../");
    $dotenv->load();
}

$gateway = new Braintree_Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'zcmwctmyhv5mtks2',
    'publicKey' => 'kdtkd4xcmf6qrw7j',
    'privateKey' => '2b246787d67f8a003e4b2051040eab98'
]);

if(isset($_POST["subscribe_plan"])){
    
// $amount = $_POST["amount"];
$nonce = $_POST["payment_method_nonce"];
$planId = $_POST["subscribe_plan"];
var_dump($planId);
// $result = $gateway->transaction()->sale([
//     'amount' => $amount,
//     'paymentMethodNonce' => $nonce,
//     'options' => [
//         'submitForSettlement' => true
//     ]
// ]);

$result = $gateway->customer()->create(array(
    'paymentMethodNonce' => $nonce
));
var_dump($result->customer);
echo "END GE";
echo "PAYMENEET";

//if credit catd
$token = $result->customer->creditCards[0]->token;

//ifp aypal
//$token = $result->customer->paypalAccounts[0]->token;


// $result = Braintree_Subscription::create(array(
//   'paymentMethodToken' => $token,
//   'planId' => 'planId that created in Braintree',
// ));

$result = $gateway->subscription()->create([
    'paymentMethodToken' => $token,
    'planId' => $planId,
    'merchantAccountId' => 'klfreelance'
  ]);

  
if ($result->success || !is_null($result->transaction)) {
    $transaction = $result->transaction;
    echo "SACCESS";
} else {
    $errorString = "";

    foreach($result->errors->deepAll() as $error) {
        $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
    }
    echo "ERRor <br>";
    echo $errorString;
}


var_dump($result);  
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

            <form method="post" id="payment-form" action="http://localhost/PaypalBrainTreeTEST/">
                <section>
                    <!-- <label for="amount">
                        <span class="input-label">Amount</span>
                        <div class="input-wrapper amount-wrapper">
                            <input id="amount" name="amount" type="tel" min="1" placeholder="Amount" value="10">
                        </div>
                    </label> -->
 
                    <label for="plan">
                    <span class="input-label">Plan</span>
                        <div class="input-wrapper amount-wrapper">
                            <select name="subscribe_plan">
                                <option value="2bgb">Montly</option>
                                <option value="g8tm">Yearly</option>
                            </select>
                        </div>                   
                    </label>                   

                    <div class="bt-drop-in-wrapper">
                        <div id="bt-dropin"></div>
                    </div>
                </section>

                <input id="nonce" name="payment_method_nonce" type="hidden" />
                <button class="button" type="submit"><span>Test Transaction</span></button>
            </form>
<script src="https://js.braintreegateway.com/web/dropin/1.10.0/js/dropin.min.js"></script>
    <script>
        var form = document.querySelector('#payment-form');
        var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";

        braintree.dropin.create({
          authorization: client_token,
          selector: '#bt-dropin',
          paypal: {
            flow: 'vault'
          }
        }, function (createErr, instance) {
          if (createErr) {
            console.log('Create Error', createErr);
            return;
          }
          form.addEventListener('submit', function (event) {
            event.preventDefault();

            instance.requestPaymentMethod(function (err, payload) {
              if (err) {
                console.log('Request Payment Method Error', err);
                return;
              }

              // Add the nonce to the form and submit
              document.querySelector('#nonce').value = payload.nonce;
              form.submit();
            });
          });
        });
    </script>
</body>
</html>