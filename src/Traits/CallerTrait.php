<?php

namespace GoWebPro\MyKassa\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use GoWebPro\MyKassa\Exceptions\InvalidPaidOrder;
use GoWebPro\MyKassa\Exceptions\InvalidSearchOrder;

trait CallerTrait
{
    /**
     * @param Request $request
     * @return mixed
     *
     * @throws InvalidSearchOrder
     */
    public function callSearchOrder(Request $request)
    {
        if (is_null(config('mykassa.searchOrder'))) {
            throw new InvalidSearchOrder();
        }

        return App::call(config('mykassa.searchOrder'), ['order_id' => $request->input('MERCHANT_ORDER_ID')]);
    }

    /**
     * @param Request $request
     * @param $order
     * @return mixed
     * @throws InvalidPaidOrder
     */
    public function callPaidOrder(Request $request, $order)
    {
        if (is_null(config('mykassa.paidOrder'))) {
            throw new InvalidPaidOrder();
        }

        return App::call(config('mykassa.paidOrder'), ['order' => $order]);
    }
}
