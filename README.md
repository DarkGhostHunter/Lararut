![Loïc Mermilliod - Unsplash (UL) #H6KJ2D0LphU](https://images.unsplash.com/photo-1490782300182-697b80ad4293?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1280&h=400&q=80)

[![Latest Stable Version](https://poser.pugx.org/darkghosthunter/lararut/v/stable)](https://packagist.org/packages/darkghosthunter/lararut) [![License](https://poser.pugx.org/darkghosthunter/lararut/license)](https://packagist.org/packages/darkghosthunter/lararut)
![](https://img.shields.io/packagist/php-v/darkghosthunter/lararut.svg) [![Build Status](https://travis-ci.com/DarkGhostHunter/Lararut.svg?branch=master)](https://travis-ci.com/DarkGhostHunter/Lararut) [![Coverage Status](https://coveralls.io/repos/github/DarkGhostHunter/Lararut/badge.svg?branch=master)](https://coveralls.io/github/DarkGhostHunter/Lararut?branch=master) [![Maintainability](https://api.codeclimate.com/v1/badges/b07f8f752242ba1f2831/maintainability)](https://codeclimate.com/github/DarkGhostHunter/Lararut/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/b07f8f752242ba1f2831/test_coverage)](https://codeclimate.com/github/DarkGhostHunter/Lararut/test_coverage)

# Lararut

This package integrates the [RutUtils](https://github.com/DarkGhostHunter/RutUtils/) package, allowing manipulation of RUTs in your PHP project, with Laravel.

Additionally, it includes 4 new rules to validate RUT data conveniently.

## Requirements

- PHP 7.1.3+
- Laravel 5.7+ (Lumen *may* work)

## Installation

Fire up Composer and require it into your project:

```bash
composer require darkghosthunter/lararut
```

## Usage

This package is just a Service Provider and Facade for RutUtils, but it also provides a Validator.

### `Rut` Facade

This package registers a Facade using the `Rut` class, so you can access all the methods available for the RutUtils package from just calling it.

For example, you can use the Rut Facade to transform the `rut` attribute of an Eloquent Model (like the [User Model](https://github.com/laravel/laravel/tree/master/app/User.php)) into a flexible Rut instance.

```php
<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Rut;

class User extends Authenticatable
{
   // ...
   
   /**
    * Set the Rut attribute as a Rut instance 
    * 
    * @param $value
    */
   public function setRutAttribute($value)
   {
       $this->attributes['rut'] = Rut::make($value);
   }
   
}
```

Or use it in your Controllers to, in this case, only filter RUTs which are valid.

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rut;

class DoesSomethingController extends Controller
{
    
    /**
     * Check the RUTs from the Request 
     * 
     * @param Request $request
     * @return string
     */
    public function checkManyUsers(Request $request)
    {
        // Validate the input...
        
        $validRuts = Rut::filter($request->ruts);
        
        return 'Valid RUTs:' . implode(' ,', $validRuts) . '.';
    }
}
```

Since it's a Facade, you can also use it for [testing with Laravel](https://laravel.com/docs/5.7/mocking#mocking-facades).

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Rut;

class RegisterControllerTest extends TestCase
{
    public function testUserRegistersFamily()
    {
        Rut::shouldReceive('filter')
            ->once()
            ->with($this->isType('array'))
            ->andReturn(true);
        
        // ...
    }
}
```

Check the [RutUtils documentation](https://github.com/DarkGhostHunter/RutUtils/blob/master/README.md) to see all the available methods.

### Helper

Sometimes you want to quickly create a RUT from scratch anywhere in your code. You can use the included quick helper `rut()` to do so, which just serves as an alias to `Rut::make`.

```php
<?php

namespace App\Http\Listeners;

use Illuminate\Auth\Events\Lockout;
use App\Notifications\ProbablyForgotPassword;
use App\User;

class LogFailedAttempt
{
    /**
     * Handle the event.
     *
     * @param  Lockout  $event
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     * @return void
     */
    public function handle(Lockout $event)
    {
        // Get the RUT from the request input
        $rut = rut($event->request->input('rut'));
        
        // If the user who tried exists in the database
        if ($user = User::whereNum($rut->num)->first()) {
            
            // Help him with a link to reset his password
            $user->notify(new ProbablyForgotPassword());
        }
    }
}
```

### Validation rules

This package includes four rules, `is_rut`, `is_rut_strict`, `is_rut_equal` and `rut_exists`.

#### `is_rut`

This checks if the RUT being passed is a valid RUT string. This automatically cleans the RUT from anything except numbers and Verification Digit, and checks if the RUT is valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'required|is_rut'
]);

echo $validator->passes(); // true
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid, and false if at least one is invalid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', 'thisisnotarut']
], [
    'rut' => 'required|array|is_rut'
]);

echo $validator->fails(); // true
```


#### `is_rut_strict` 

This works the same as `is_rut`, but it will validate RUTs that are also using the correct RUT format: with thousand separator and a hyphen before the Validation Digit.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'required|is_rut_strict'
]);

echo $validator->fails(); // false
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid.

#### `is_rut_equal` 

This will check if the RUT is equal to another RUT, like for example, one inside your Database. They will be cleaned.
 
```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|is_rut_equal:12343580K' 
]);

echo $validator->fails(); // false
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid.

#### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for RUT Number and Verification Digit.

For this to work you need to set the table to look for, Number column and Verification Digit column. Optionally, you can set the connection using dot notation.
 
```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_exists:mysql.users,rut_num,rut_vd' 
]);

echo $validator->fails(); // false
```

Since this also checks if the RUT is valid, it will return false if its not, or the RUT doesn't exists in the database.

> Having separated columns for the RUT number and verification digits is usually the best approach to persist them. The number can be saved as 4 byte unsigned `int`, and the latter as a 1 byte `varchar` (1 character length).

## License

This package is licenced by the [MIT License](LICENSE).