<?php
namespace App\Services;
use Illuminate\Http\Client\Request ;
use Illuminate\Support\Facades\Http;
use App\Models\Wallet;
class PayMobServices {
    private $PAYMOB_API_KEY ;
    private $token ;
    private $id;
    private $integration_id ;
    private $price;
    private $iframe_token;
    public function __construct($price)
    {
        $this->PAYMOB_API_KEY="ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2T1RRME5UY3lMQ0p1WVcxbElqb2lhVzVwZEdsaGJDSjkuREl4bHJlU01DZHNuUDlPQjlVdklJMTdHRmZ4UkFUZ3dfSm4tT1NMWV9Xand2amx0Z3U4MHo0bWJYdHJ3QkpFOUh4aVJUYnk3WVh6VHFOWEs4RzdZWnc="; // change it
        $this->id=null;
        $this->integration_id=4421413;//change it
        $this->price=$price;
        $this->iframe_token=null;
    }

    public function getToken()
    {
        $url="https://accept.paymob.com/api/auth/tokens";
	    $response = Http::withHeaders(['content-type' => 'application/json'])->post($url,
		["api_key" => $this->PAYMOB_API_KEY]);

        if(isset($response->json()['token']))
        {
            $this->token= $response->json()['token'];
            return $response->json()['token'];

        }
        return false;
    }

    public function get_id()
    {
            $this->token = $this->getToken();
            $response_final = Http::withHeaders(['content-type' => 'application/json'])
            ->post('https://accept.paymob.com/api/ecommerce/orders',
            ["auth_token" => $this->token,
                 "delivery_needed" => "false",
                 "amount_cents" => $this->price * 100,
                 "items" => []]);
            if(isset($response_final->json()['id']))
            {
                $this->id=$response_final->json()['id'];
                return $this->id;
            }
        
    }
    public function make_order($user)
    {

        $url ="https://accept.paymob.com/api/acceptance/payment_keys";
	    $response_final_final = Http::withHeaders(['content-type' => 'application/json'])->post($url,
             ["auth_token" =>
		$this->token,
             "expiration" => 36000,
             "amount_cents" =>$this->price *100,
             "delivery_needed"=> "false",
             "currency"=> "EGP",
             "items"=> [

                ],

                "billing_data" => [
                    "apartment" => '45', //example $dataa->appartment
                    "email" => $user->email, //example $dataa->email
                    "floor" => '5',
                    "first_name" => $user->first_name,
                    "street" => "NA",
                    "building" => "NA",
                    "phone_number" => $user->client->phone,
                    "shipping_method" => "NA",
                    "postal_code" => "NA",
                    "city" => "cairo",
                    "country" => "NA",
                    "last_name" => $user->last_name,
                    "state" => "NA"
                ],


             "order_id" => $this->id, // this order id created by paymob
             "integration_id" =>$this->integration_id  ]);
        if(isset($response_final_final->json()['token']))
        {
            
        //order_id==payment_id in table wallet

      $wallet = new Wallet([
      'payment_id' => $this->id,
      'payment_status' => "order",
       'user_id' => $user->id,
        'amount' => 0,
        ]);
        $wallet->save();
            $this->iframe_token = $response_final_final->json()['token'];
            return $this->iframe_token;
        }

    }





}
