<?php

namespace App\Http\Controllers;

use App\Models\Paytm as ModelsPaytm;
use Illuminate\Http\Request;
use App\Order;
use PaytmWallet;
use Session;


class PaytmController extends Controller
{
    /**
     * Redirect the user to the Payment Gateway.
     *
     * @return Response
     */
    
    
 
    public function paytmPayment(Request $request)
    {
  
        $payment = PaytmWallet::with('receive');
        $payment->prepare([
          'order' => rand(),
          'user' => rand(10,1000),
          'mobile_number' => '123456789',
          'email' => 'paytmtest@gmail.com',
          'amount' => $request->amount,
          'callback_url' => route('paytm.callback'),
        ]);
        
        return $payment->receive();
       
    }


    /**
     * Obtain the payment information.
     *
     * @return Object
     */
    
    public function paytmCallback(Request $request)
    {
        
        $transaction = PaytmWallet::with('receive');
        $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm
        $transaction->getOrderId(); // Get order id
        $transaction->getTransactionId(); // Get transaction id
        if($transaction->isSuccessful())
        {
          //Transaction Successful
          $order = new Order();
          $order->order_id = $transaction->getOrderId();
          $order->status = 'Done';
          $order->price=$transaction->getAmount();
          $order->transaction_id=$transaction->getTransactionId();
          $order->save();
          return view('paytm.paytm-success-page');
        }else if($transaction->isFailed())
        {
          //Transaction Failed
          $order = new Order();
          $order->order_id = $transaction->getOrderId();
          $order->status = 'Failed';
          $order->price=$transaction->getAmount();
          $order->transaction_id=$transaction->getTransactionId();
          $order->save();
          return view('paytm.paytm-fail');
        }else if($transaction->isOpen()){
          //Transaction Open/Processing
          return view('paytm.paytm-fail');
        }
        $transaction->getResponseMessage(); //Get Response Message If Available
     


     

        
    }

    

    /**
     * Paytm Payment Page
     *
     * @return Object
     */
    public function paytmPurchase()
    {
        return view('paytm.payment-page');
    } 
}


