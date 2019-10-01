<?php

namespace App\Http\Controllers;

use App\Helpers\Alert;
use Illuminate\Http\Request;
use Stripe\Charge;
use Stripe\Stripe;

class PaymentController extends Controller
{

    public function index()
    {
        return view('welcome');
    }

    public function make_payments(Request $request)
    {
        try{
            // Token is created using Checkout or Elements!
            // Get the payment token ID submitted by the form:
            $token = $request->input('stripeToken');

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Add customer to stripe
            $customer = \Stripe\Customer::create([
                'email' => $request->input('email'),
                'source' => $token,
                'name'	 => $request->input('first_name') . " " .$request->input('last_name'),
                'phone'	 => $request->input('mobile'),
                'address' => [
                    'city' => 'Mymensingh',
                    'country' => 'bd',
                    'line1' => '34, Muktijoddha Sarani',
                    'line2' => '2nd floor, Choto Bazar',
                    'postal_code' => '2200',
                    'state' => 'Mymensingh'
                ]
            ]);

            $charge = Charge::create([
                'customer' => $customer->id,
                'amount' => 1.9 * 100, // convert cents to dollar
                'currency' => 'usd',
                'description' => 'Example charge description',
                'statement_descriptor' => 'Custom chrg descriptor',
                'metadata' => [
                    'order_id' => 505,
                    "invoice_id" => 'II012345',
                    "customer_id"	=> '501',
                    "address"		=> "Mymensingh Dhaka Bangladesh",
                    "billing_zip"	=> 2200,
                ],
            ]);

            // Check that it was paid:
            if ($charge->paid == true) {
                $request->session()->flash('alert', Alert::alert('success', 'Payment successfully complete.'));
                return redirect()->back();
            } else {
                $request->session()->flash('alert', Alert::alert('warning', 'Something went wrong!'));
                return redirect()->back();
            }
        } catch(\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            // echo 'Status is:' . $e->getHttpStatus() . '\n';
            // echo 'Type is:' . $e->getError()->type . '\n';
            // echo 'Code is:' . $e->getError()->code . '\n';
            // param is '' in this case
            // echo 'Param is:' . $e->getError()->param . '\n';
            // echo 'Message is:' . $e->getError()->message . '\n';
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
            // (maybe you changed API keys recently)
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
            // yourself an email
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $request->session()->flash('alert', Alert::alert('warning', $e->getMessage()));
            return redirect()->back();
        }
    }
}
