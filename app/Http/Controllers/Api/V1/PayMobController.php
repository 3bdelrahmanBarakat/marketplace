<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;
use App\Models\Client;
use PayMob\Facades\PayMob;
use Illuminate\Http\Request;
use App\Models\PromotionPlan;
use App\Services\PayMobServices;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
class PayMobController extends Controller
{


    public function pay(){
        $user = User::where('id',auth()->user()->id)->first();
        $order = PromotionPlan::whereId(request()->input('promotion_plan_id'))->first();
        $price = request()->input('price');
        $payMob = new PayMobServices($price);
        $payMob->get_id();
        $url=$payMob->make_order($user);
        $url_token = "https://accept.paymobsolutions.com/api/acceptance/iframes/810422?payment_token=".$url;
        return response()->json($url_token);
    }

 public function callback(Request $request)
    {
        //this call back function its return the data from paymob and we show the full response and we checked if hmac is correct means successfull payment

        $data = $request->all();
        ksort($data);
        $hmac = $data['hmac'];
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if(in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        $secret = "C4AEED85D2EECC0086A4BAD1FBF8DDA4";
        $hased = hash_hmac('sha512', $connectedString, $secret);
        if ($hased == $hmac) {
            //this below data used to get the last order created by the customer and check if its exists to


             $wallet= Wallet::where('payment_id',$data['id'])->first();
            $status = $data['success'];
            // $pending = $data['pending'];

            if ($status == "true") {
                $amount=$data['amount_cents'] / 100;
               
                //here we checked that the success payment is true and we updated the data base and empty the cart and redirct the customer to thankyou page



                    $wallet = Wallet::where('payment_id',$data['order'])->first();
                    $user=User::whereId($wallet->user_id)->first();
                    $user->update([
                        'wallet'=>$user->wallet+$amount
                    ]);

                    $wallet->update([
                     'amount'=>0,
                     'payment_status'=>"been shipped"
                    ]);


                return response()->json(['message' => 'thank you']);      


            }
            else {
                $wallet->update([
                    'payment_status' => "Failed",
                    'amount'=> 0,
                ]);


                return response()->json(['message' => 'Something went wrong. Please try again.'], 400);

            }

        }else {
            return response()->json(['message' => 'Invalid HMAC. Something went wrong. Please try again.'], 400);
        }
    }

}
