![Loïc Mermilliod - Unsplash (UL) #H6KJ2D0LphU](https://images.unsplash.com/photo-1490782300182-697b80ad4293?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1280&h=400&q=80)

# Lararut

RutUtils integration for Laravel.

This package allows you to use [RutUtils](https://github.com/DarkGhostHunter/RutUtils/), a package for manipulating RUTs in your PHP project, with Laravel.

Additionally, it includes 4 new rules to validate RUT data conveniently.

## Requirements

- Laravel 5.7+ (Lumen *may* work)

## Installation

Fire up Composer and require it into your project:

```bash
composer require darkghosthunter/lararut
```

## Usage

This package is just a Service Provider and Facade for RutUtils, but it also provides a Validator.

### `Rut` Facade

This package registers a Facade using the Rut::class, so you can access all the methods available for the RutUtils package.

For example you can use the Rut Facade to transform the `rut` attribute of a Eloquent Model (like the User) into a flexible Rut instance.  

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

Or use in your Controllers to, in this case, only filter Ruts which are valid.

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

Since it's a Facade, you can also use it for testing with Laravel. Check the [RutUtils documentation](https://github.com/DarkGhostHunter/RutUtils/blob/master/README.md) to see all the available methods.

### Validation rules

This package includes two four rules, `is_rut`, `is_rut_strict`, `is_rut_equal` and `rut_exists`.

#### `is_rut`

This checks if the RUT being passed is a valid RUT string. This automatically cleans the RUT from anything except numbers and Verification Digit, and sees is the resulting RUT is valid.

```php
<?php

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'required|is_rut'
]);

echo $validator->fails(); // false
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid.

#### `is_rut_strict` 

This works the same as `is_rut`, but it will validate RUTs only using the correct format with thousand separator and a hyphen before the Validation Digit.

```php
<?php

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'required|is_rut_strict'
]);

echo $validator->fails(); // true
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid.

#### `is_rut_equal` 

This will check if the RUT is equal to another RUT, like for example, one inside your Database.
 
```php
<?php
$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|is_rut_equal:12343580K' 
]);

echo $validator->fails(); // false
```

It also accepts an `array` of RUTs. In that case, `is_rut` will return true if all of the RUTs are valid.

#### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exist` in case your database has separated columns for RUT Number and Verification Digit.

For this to work you need to issue your table, connection (optionally), Number column and Verification Digit column.
 
```php
<?php
$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_exist:mysql.users,rut_num,rut_vd' 
]);

echo $validator->fails(); // false
```

Since this also checks if the RUT is valid, it will return false if its not, or the RUT doesn't exists in the database.

## License

This package is licenced by the [MIT License](LICENSE).