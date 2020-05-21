<?php

namespace GoWebPro\MyKassa\Test;

use Illuminate\Http\Request;
use GoWebPro\MyKassa\Exceptions\InvalidPaidOrder;
use GoWebPro\MyKassa\Exceptions\InvalidSearchOrder;
use GoWebPro\MyKassa\Test\Fake\Order;

class MyKassaTest extends TestCase
{
    /** @test */
    public function test_env()
    {
        $this->assertEquals('testing', $this->app['env']);
    }

    /**
     * Create test request with custom method and add signature.
     *
     * @param bool $signature
     * @return Request
     */
    protected function create_test_request($signature = false)
    {
        $params = [
            'MERCHANT_ID' => '12345',
            'AMOUNT' => '100',
            'MYKASSA_ID' => '11',
            'MERCHANT_ORDER_ID' => '10',
        ];

        if ($signature === false) {
            $params['SIGN'] = $this->mykassa->getSignature($params['MERCHANT_ID'], $params['AMOUNT'], $this->app['config']->get('mykassa.secret_key_second'), $params['MERCHANT_ORDER_ID']);
        } else {
            $params['SIGN'] = $signature;
        }

        $request = new Request($params);

        return $request;
    }

    /** @test */
    public function check_if_allow_remote_ip()
    {
        $this->assertTrue(
            $this->mykassa->allowIP('127.0.0.1')
        );

        $this->assertFalse(
            $this->mykassa->allowIP('0.0.0.0')
        );
    }

    /** @test */
    public function compare_form_signature()
    {
        $this->assertEquals(
            'e9759d5cbc80ceb8716d06d7e2adc348',
            $this->mykassa->getFormSignature('12345', '100', 'secret_key', '10')
        );
    }

    /** @test */
    public function compare_signature()
    {
        $this->assertEquals(
            '7f590bc40563dc9ff96269e586ba6e65',
            $this->mykassa->getSignature('12345', '100', 'secret_key_second', '10')
        );
    }

    /** @test */
    public function generate_pay_url()
    {
        $url = $this->mykassa->getPayUrl(100, 10, 'example@gmail.com');

        $this->assertStringStartsWith($this->app['config']->get('mykassa.pay_url'), $url);
    }

    /** @test */
    public function compare_request_signature()
    {
        $params = [
            'MERCHANT_ID' => '12345',
            'AMOUNT' => '100',
            'MERCHANT_ORDER_ID' => '10',
        ];

        $this->assertEquals(
            '7f590bc40563dc9ff96269e586ba6e65',
            $this->mykassa->getSignature($params['MERCHANT_ID'], $params['AMOUNT'], $this->app['config']->get('mykassa.secret_key_second'), $params['MERCHANT_ORDER_ID'])
        );
    }

    /** @test */
    public function pay_order_form_validate_request()
    {
        $request = $this->create_test_request();
        $this->assertTrue($this->mykassa->validate($request));
    }

    /** @test */
    public function validate_signature()
    {
        $request = $this->create_test_request();
        $this->assertTrue($this->mykassa->validate($request));
        $this->assertTrue($this->mykassa->validateSignature($request));

        $request = $this->create_test_request('invalid_signature');
        $this->assertTrue($this->mykassa->validate($request));
        $this->assertFalse($this->mykassa->validateSignature($request));
    }

    /** @test */
    public function test_order_need_callbacks()
    {
        $request = $this->create_test_request();
        $this->expectException(InvalidSearchOrder::class);
        $this->mykassa->callSearchOrder($request);

        $request = $this->create_test_request();
        $this->expectException(InvalidPaidOrder::class);
        $this->mykassa->callPaidOrder($request, ['order_id' => '12345']);
    }

    /** @test */
    public function search_order_has_callbacks_fails()
    {
        $this->app['config']->set('mykassa.searchOrder', [Order::class, 'SearchOrderFilterFails']);
        $request = $this->create_test_request();
        $this->assertFalse($this->mykassa->callSearchOrder($request));
    }

    /** @test */
    public function paid_order_has_callbacks()
    {
        $this->app['config']->set('mykassa.searchOrder', [Order::class, 'SearchOrderFilterPaid']);
        $this->app['config']->set('mykassa.paidOrder', [Order::class, 'PaidOrderFilter']);
        $request = $this->create_test_request();
        $this->assertTrue($this->mykassa->callPaidOrder($request, ['order_id' => '12345']));
    }

    /** @test */
    public function paid_order_has_callbacks_fails()
    {
        $this->app['config']->set('mykassa.paidOrder', [Order::class, 'PaidOrderFilterFails']);
        $request = $this->create_test_request();
        $this->assertFalse($this->mykassa->callPaidOrder($request, ['order_id' => '12345']));
    }

    /** @test */
    public function payOrderFromGate_SearchOrderFilter_fails()
    {
        $this->app['config']->set('mykassa.searchOrder', [Order::class, 'SearchOrderFilterFails']);
        $request = $this->create_test_request('error');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->assertEquals($this->app['config']->get('mykassa.errors.validateOrderFromHandle'), $this->mykassa->handle($request));
    }

    /** @test */
    public function payOrderFromGate_method_pay_SearchOrderFilterPaid()
    {
        $this->app['config']->set('mykassa.searchOrder', [Order::class, 'SearchOrderFilterPaidforPayOrderFromGate']);
        $this->app['config']->set('mykassa.paidOrder', [Order::class, 'PaidOrderFilter']);
        $request = $this->create_test_request();

        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->assertEquals('YES', $this->mykassa->handle($request));
    }
}
