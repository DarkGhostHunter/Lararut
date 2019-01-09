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

### Helpers

This package also [includes `RutUtils` helpers](https://github.com/DarkGhostHunter/RutUtils/#global-helper-functions) file, which allows you to use simple functions anywhere in your code.

Additionally, this includes `rut()` function to quickly create a RUT from scratch, which just serves as an alias to `Rut::make`.

```php
<?php

namespace App\Http\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProbablyForgotPassword;
use App\Notifications\SupportReadyToHelp;
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
        if ($user = User::where('rut_num', $rut->num)->first()) {
            
            // Help sending him a link to reset his password
            $user->notify(new ProbablyForgotPassword());
        }
    }
}
```

### Validation rules

This package includes four rules, `is_rut`, `is_rut_strict`, `is_rut_equal` and `rut_exists`.

#### `is_rut`

This checks if the RUT being passed is a valid RUT string. This automatically **cleans the RUT** from anything except numbers and verification digit, and then checks if the RUT is valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'required|is_rut'
]);

echo $validator->passes(); // true

$validator = Validator::make([
    'rut' => '65.00!!!390XXXX2'
], [
    'rut' => 'required|is_rut'
]);

echo $validator->passes(); // true
```

This come handy in situations when the user presses a wrong button into an input, and allows you to center on the value itself rather than sanitizing the value.

The rule also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid, and false if at least one is invalid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', 'thisisnotarut']
], [
    'rut' => 'required|array|is_rut'
]);

echo $validator->fails(); // true

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', '20881410-9']
], [
    'rut' => 'required|array|is_rut'
]);

echo $validator->fails(); // false
```

#### `is_rut_strict` 

This works the same as `is_rut`, but it will validate RUTs that are also using the correct RUT format: with thousand separator and a hyphen before the Validation Digit.

Since it does not cleans the value, it will return false even if there is a misplaced character.

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

This rule also accepts an `array` of RUTs. In that case, `is_rut_strict` will return true if all of the RUTs are properly formatted and valid.

#### `is_rut_equal` 

This will check if the RUT is equal to another RUT, like for example, one inside your Database. Both will be cleaned before the validation procedure.
 
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

It also accepts an `array` of RUTs, which saves you to do multiple `is_rut_equal`. In these cases, `is_rut_equal` will return true if all of the RUTs are valid and equal to each other.


```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|is_rut_equal:12343!580K,12.343.580-K' 
]);

echo $validator->fails(); // false
```

#### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for RUT Number and Verification Digit.

For this to work you need to set the table to look for, the *RUT number* column and *RUT verification digit* column. Optionally, you can set the connection using dot notation.
 
```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_exists:mysql.users,rut_num,rut_vd' 
]);

echo $validator->passes(); // false
```

Since this also checks if the RUT is valid, it will return false if its not, or the RUT doesn't exists in the database.

The rule will automatically set to uppercase the verification digit column, so it won't matter if in your column you manage `k` as lowercase.

> Having separated columns for the RUT number and verification digits is usually the best approach to persist them. The number can be saved as 4 byte unsigned `int`, and the latter as a 1 byte `varchar` (1 character length).

## License

This package is licenced by the [MIT License](LICENSE).