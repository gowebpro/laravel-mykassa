<?php

namespace GoWebPro\MyKassa;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GoWebPro\MyKassa\Traits\CallerTrait;
use GoWebPro\MyKassa\Traits\ValidateTrait;

class MyKassa
{
    use ValidateTrait;
    use CallerTrait;

    //

    /**
     * MyKassa constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * @param $amount
     * @param $order_id
     * @param null $phone
     * @param null $email
     * @param array $user_parameters
     * @return string
     */
    public function getPayUrl($amount, $order_id, $phone = null, $email = null, $user_parameters = [])
    {
        // Url to init payment on MyKassa
        $url = config('mykassa.pay_url');

        // Array of url query
        $query = [];

        // If user parameters array doesn`t empty
        // add parameters to payment query
        if (! empty($user_parameters)) {
            foreach ($user_parameters as $parameter => $value) {
                $query['us_'.$parameter] = $value;
            }
        }

        // Project id (merchat id)
        $query['m'] = config('mykassa.project_id');

        // Amount of payment
        $query['oa'] = $amount;

        // Order id
        $query['o'] = $order_id;

        // User phone (optional)
        if (! is_null($phone)) {
            $query['phone'] = $phone;
        }

        // User email (optional)
        if (! is_null($email)) {
            $query['email'] = $email;
        }

        // Locale for payment form
        $query['lang'] = config('mykassa.locale', 'ru');

        // Payment currency
        if (! is_null(config('mykassa.currency'))) {
            $query['i'] = config('mykassa.currency');
        }

        $query['s'] = $this->getFormSignature(
            config('mykassa.project_id'),
            $amount,
            config('mykassa.secret_key'), $order_id
        );

        // Merge url ang query and return
        return $url.'?'.http_build_query($query);
    }

    /**
     * @param $amount
     * @param $order_id
     * @param null $phone
     * @param null $email
     * @param array $user_parameters
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToPayUrl($amount, $order_id, $phone = null, $email = null, $user_parameters = [])
    {
        return redirect()->away($this->getPayUrl($amount, $order_id, $phone, $email, $user_parameters));
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function allowIP($ip)
    {
        // Allow local ip
        if ($ip == '127.0.0.1' || config('mykassa.check_ip_skip')) {
            return true;
        }

        return in_array($ip, config('mykassa.allowed_ips'));
    }

    /**
     * @param $project_id
     * @param $amount
     * @param $secret
     * @param $order_id
     * @return string
     */
    public function getFormSignature($project_id, $amount, $secret, $order_id)
    {
        $hashStr = $project_id.':'.$amount.':'.$secret.':'.$order_id;

        return md5($hashStr);
    }

    /**
     * @param $project_id
     * @param $amount
     * @param $secret
     * @param $order_id
     * @return string
     */
    public function getSignature($project_id, $amount, $secret, $order_id)
    {
        $hashStr = $project_id.':'.$amount.':'.$secret.':'.$order_id;

        return md5($hashStr);
    }

    /**
     * @param Request $request
     * @return string
     * @throws Exceptions\InvalidPaidOrder
     * @throws Exceptions\InvalidSearchOrder
     */
    public function handle(Request $request)
    {
        // Validate request from MyKassa
        if (! $this->validateOrderFromHandle($request)) {
            return $this->responseError('validateOrderFromHandle');
        }

        // Search and get order
        $order = $this->callSearchOrder($request);

        if (! $order) {
            return $this->responseError('searchOrder');
        }

        // If order already paid return success
        if (Str::lower($order['_orderStatus']) === 'paid') {
            return $this->responseYES();
        }

        // PaidOrder - update order info
        // if return false then return error
        if (! $this->callPaidOrder($request, $order)) {
            return $this->responseError('paidOrder');
        }

        // Order is paid and updated, return success
        return $this->responseYES();
    }

    /**
     * @param $error
     * @return string
     */
    public function responseError($error)
    {
        return config('mykassa.errors.'.$error, $error);
    }

    /**
     * @return string
     */
    public function responseYES()
    {
        // Must return 'YES' if paid successful
        // http://www.mykassa.org/page/api#goto2

        return 'YES';
    }
}
