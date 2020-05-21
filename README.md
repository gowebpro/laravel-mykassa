# Laravel payment processor package for MyKassa gateway

[![Latest Stable Version](https://poser.pugx.org/gowebpro/laravel-mykassa/v/stable)](https://packagist.org/packages/gowebpro/laravel-mykassa)
[![Build Status](https://travis-ci.org/gowebpro/laravel-mykassa.svg?branch=master)](https://travis-ci.org/gowebpro/laravel-mykassa)
[![StyleCI](https://github.styleci.io/repos/165751650/shield?branch=master)](https://github.styleci.io/repos/165751650)
[![CodeFactor](https://www.codefactor.io/repository/github/gowebpro/laravel-mykassa/badge)](https://www.codefactor.io/repository/github/gowebpro/laravel-mykassa)
[![Total Downloads](https://img.shields.io/packagist/dt/gowebpro/laravel-mykassa.svg?style=flat-square)](https://packagist.org/packages/gowebpro/laravel-mykassa)
[![License](https://poser.pugx.org/gowebpro/laravel-mykassa/license)](https://packagist.org/packages/gowebpro/laravel-mykassa)

Accept payments via MyKassa ([mykassa.org](http://www.mykassa.org/)) using this Laravel framework package ([Laravel](https://laravel.com)).

- receive payments, adding just the two callbacks

#### Laravel >= 7.*, PHP >= 7.2.5

> The package for Laravel 5.* or 6.* don't have

## Installation

Require this package with composer.

``` bash
composer require gowebpro/laravel-mykassa
```

If you don't use auto-discovery, add the ServiceProvider to the providers array in `config/app.php`

```php
GoWebPro\MyKassa\MyKassaServiceProvider::class,
```

Add the `MyKassa` facade to your facades array:

```php
'MyKassa' => GoWebPro\MyKassa\Facades\MyKassa::class,
```

Copy the package config to your local config with the publish command:
``` bash
php artisan vendor:publish --provider="GoWebPro\MyKassa\MyKassaServiceProvider"
```

## Configuration

Once you have published the configuration files, please edit the config file in `config/mykassa.php`.

- Create an account on [mykassa.org](http://www.mykassa.org)
- Add your project, copy the `project_id`, `secret_key` and `secret_key_second` params and paste into `config/mykassa.php`
- After the configuration has been published, edit `config/mykassa.php`
- Set the callback static function for `searchOrder` and `paidOrder`
- Create route to your controller, and call `MyKassa::handle` method
 
## Usage

1) Generate a payment url or get redirect:

```php
$amount = 100; // Payment`s amount

$url = MyKassa::getPayUrl($amount, $order_id);

$redirect = MyKassa::redirectToPayUrl($amount, $order_id);
```

You can add custom fields to your payment:

```php
$rows = [
    'time' => Carbon::now(),
    'info' => 'Local payment'
];

$url = MyKassa::getPayUrl($amount, $order_id, $email, $phone, $rows);

$redirect = MyKassa::redirectToPayUrl($amount, $order_id, $email, $phone, $rows);
```

`$email` and `$phone` can be null.

2) Process the request from MyKassa:
``` php
MyKassa::handle(Request $request)
```

## Important

You must define callbacks in `config/mykassa.php` to search the order and save the paid order.


``` php
'searchOrder' => null  // MyKassaController@searchOrder(Request $request)
```

``` php
'paidOrder' => null  // MyKassaController@paidOrder(Request $request, $order)
```

## Example

The process scheme:

1. The request comes from `mykassa.org` `GET` / `POST` `http://yourproject.com/mykassa/result` (with params).
2. The function`MyKassaController@handlePayment` runs the validation process (auto-validation request params).
3. The method `searchOrder` will be called (see `config/mykassa.php` `searchOrder`) to search the order by the unique id.
4. If the current order status is NOT `paid` in your database, the method `paidOrder` will be called (see `config/mykassa.php` `paidOrder`).

Add the route to `routes/web.php`:
``` php
 Route::get('/mykassa/result', 'MyKassaController@handlePayment');
```

> **Note:**
don't forget to save your full route url (e.g. http://example.com/mykassa/result ) for your project on [mykassa.org](www.mykassa.org).

Create the following controller: `/app/Http/Controllers/MyKassaController.php`:

``` php
class MyKassaController extends Controller
{
    /**
     * Search the order in your database and return that order
     * to paidOrder, if status of your order is 'paid'
     *
     * @param Request $request
     * @param $order_id
     * @return bool|mixed
     */
    public function searchOrder(Request $request, $order_id)
    {
        $order = Order::where('id', $order_id)->first();

        if($order) {
            $order['_orderSum'] = $order->sum;

            // If your field can be `paid` you can set them like string
            $order['_orderStatus'] = $order['status'];

            // Else your field doesn` has value like 'paid', you can change this value
            $order['_orderStatus'] = ('1' == $order['status']) ? 'paid' : false;

            return $order;
        }

        return false;
    }

    /**
     * When paymnet is check, you can paid your order
     *
     * @param Request $request
     * @param $order
     * @return bool
     */
    public function paidOrder(Request $request, $order)
    {
        $order->status = 'paid';
        $order->save();

        //

        return true;
    }

    /**
     * Start handle process from route
     *
     * @param Request $request
     * @return mixed
     */
    public function handlePayment(Request $request)
    {
        return MyKassa::handle($request);
    }
}
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please send me an email at maksa988ua@gmail.com instead of using the issue tracker.

## Credits

- [Xmk](https://github.com/Xmk)
- [Maksa988](https://github.com/maksa988)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
