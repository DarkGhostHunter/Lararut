![Loïc Mermilliod - Unsplash (UL) #H6KJ2D0LphU](https://images.unsplash.com/photo-1490782300182-697b80ad4293?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1280&h=400&q=80)

[![Latest Stable Version](https://poser.pugx.org/darkghosthunter/lararut/v/stable)](https://packagist.org/packages/darkghosthunter/lararut) [![License](https://poser.pugx.org/darkghosthunter/lararut/license)](https://packagist.org/packages/darkghosthunter/lararut) ![](https://img.shields.io/packagist/php-v/darkghosthunter/lararut.svg) ![](https://github.com/DarkGhostHunter/Lararut/workflows/PHP%20Composer/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/DarkGhostHunter/Lararut/badge.svg?branch=master)](https://coveralls.io/github/DarkGhostHunter/Lararut?branch=master) [![Laravel Octane Compatible](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://github.com/laravel/octane)

# Lararut

This package integrates the [RutUtils](https://github.com/DarkGhostHunter/RutUtils/) package, allowing manipulation of RUTs in your PHP project, with Laravel.

Additionally, it includes 6 new rules to validate RUT data conveniently.

Check the [RutUtils documentation](https://github.com/DarkGhostHunter/RutUtils/blob/master/README.md) to see all the available methods to create, generate and validate RUTs.

> **Important** This package does not validate if the RUT is from a real person, only if it's valid. If you need that kind of functionality, you should let your application interact with the [pseudo-official API](https://portal.sidiv.registrocivil.cl/usuarios-portal/pages/DocumentRequestStatus.xhtml).

## Requirements

- PHP 7.4 or 8.0
- Laravel 7.x or 8.x

> Check older releases for older Laravel versions.

## Installation

Fire up Composer and require it into your project:

```bash
composer require darkghosthunter/lararut
```

## Validation rules

This package includes handy rules to validate RUTs incoming from your frontend. Compared to prior versions, they're are more easy to use and understand.

> Database rules will automatically normalize `K` verification _digit_ to search in the database.

### `rut`

This checks if the RUT being passed is a valid RUT string. This automatically **cleans the RUT** from anything except numbers and verification digit, and then checks if the resulting RUT is valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'rut'
]);

echo $validator->fails(); // false

$validator = Validator::make([
    'rut' => '65.00!!!390XXXX2'
], [
    'rut' => 'rut'
]);

echo $validator->fails(); // false
```

This may come handy in situations when the user presses a wrong button into an RUT input, so no need to ask the user to add hyphen or dots. Afterwards, you can use `Rut::make()` to create a new Rut instance from that input.

```php
<?php

use DarkGhostHunter\RutUtils\Rut;

$rut = Rut::make(request()->input('rut'));
``` 

The rule also accepts an `array` of RUTs. In that case, `rut` will return true if all of the RUTs are valid, and false if at least one is invalid. This may come in handy when a user is registering a lot of people into your application.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', 'thisisnotarut']
], [
    'rut' => 'rut'
]);

echo $validator->fails(); // true

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', '20881410-9']
], [
    'rut' => 'rut'
]);

echo $validator->fails(); // false
```

### `rut_strict`

This works the same as `rut`, but it will validate RUTs that are also using the correct RUT format: with thousand separator and a hyphen before the Validation Digit. This allows you to bypass any sanitization afterwards.

Since it does not cleans the value, it will return `false` even if there is one misplaced character or an invalid one.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14.328.145-0'
], [
    'rut' => 'rut_strict'
]);

echo $validator->fails(); // false

$validator = Validator::make([
    'rut' => '1.4328.145-0'
], [
    'rut' => 'rut_strict'
]);

echo $validator->fails(); // true
```

This rule also accepts an `array` of RUTs. In that case, `rut_strict` will return true if all of the RUTs are properly formatted and valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['1.4328.145-0', '12.343.580-K']
], [
    'rut.*' => 'required|rut_strict',
]);

echo $validator->fails(); // true
```

### `rut_equal` 

This will check if the RUT is equal to another RUT, like for example, the authenticated User's RUT or from another data resource. Both will be cleaned before the validation procedure.

This is handy when, for example, you need to cross-reference the RUT against other external services or API.
 
```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12343580-K'
], [
    'rut' => 'required|rut_equal:12343580K' 
]);

echo $validator->fails(); // false
```

You can use an `array` of RUTs to compare, which saves you to do multiple `rut_equal`. In these cases, `rut_equal` will return true if all of the RUTs are valid and equal to each other.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_equal:12343!580K,12.343.580-K' 
]);

echo $validator->fails(); // false
```

> If you need to compare two or more RUTs in your input, you're better using the [`same` validation rule](https://laravel.com/docs/6.x/validation#rule-same). In case of confirming a RUT, use the [`confirmed` validation rule](https://laravel.com/docs/6.x/validation#rule-confirmed).

### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for the RUT Number and Verification Digit.

For this to work you need to set the table to look for, the *RUT number* column and *RUT verification digit* column, otherwise the rule will *guess* the column names by the attribute key and appending `_num` and `_vd`, respectively.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_exists:mysql.users,rut_num,rut_vd'
]);

echo $validator->fails(); // true
```

Since this also checks if the RUT is valid (not strict), it will return `false` if its not, or the RUT doesn't exists in the database.

The rule will automatically set to uppercase the verification digit column, so it won't matter if in your column you manage `k` as lowercase.

> Having a column for the RUT number and verification digits is usually the best approach to persist them. The number can be saved as 4 byte unsigned `int`, and the latter as a 1 byte `string` (1 character length).

To customize the query, you can use the `Rule` class of Laravel, but using the method `rutExists`. Note that you can input the number and verification digit columns, or both, if you don't want to let the rule guess them, as it may incorrectly guess when using a wildcard.
 
```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => [
        'rut_1' => '12.343.580-K',
        'rut_2' => '13.871.792-5',
    ],
], [
    'rut' => [
        'required',
        Rule::rutExists('mysql.users', 'rut_num', 'rut_vd')->where('account_id', 1),
    ]
]);

echo $validator->fails(); // true
```

### `num_exists` (Database)

This validation rule checks if only the number of the RUT exists, without taking into account the verification digit. This is handy when the Database has an index in the number of the RUT, thus making this verification blazing fast.

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|num_exists:mysql.users,rut_num' 
]);

echo $validator->fails(); // true
```

You can customize the underlying query using the `numExists`. 
 
```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::numExists('mysql.users', 'rut_num')->where('account_id', 1),
    ]
]);

echo $validator->fails(); // true
```

### `rut_unique` (Database)

This works the same as the `rut_exists` rule, but instead of checking if the RUT exists in the Database, it will detect if it doesn't. This rule works just like the [Laravel's `unique` rule works](https://laravel.com/docs/6.x/validation#rule-unique).

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_unique:mysql.users,rut_num,rut_vd' 
]);

echo $validator->fails(); // true
```

You can also exclude a certain ID or records from the Unique validation. For this, you need to use the `Rule` class.

```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::rutUnique('mysql.users', 'rut_num')->ignore(request()->user()),
    ]
]);

echo $validator->fails(); // true
```

> **[Warning]** **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

### `num_unique` (Database)

This rule will check only if the **number** of the RUT doesn't exists already in the database, which is useful for Databases with an index solely on the number of the RUT. This rule also matches the [Laravel's `unique` rule works](https://laravel.com/docs/6.x/validation#rule-unique).

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|num_unique:mysql.users,rut_num' 
]);

echo $validator->fails(); // true
```

You can also exclude a certain ID or records from the Unique validation. For this, you need to use the `Rule` class.

```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::numUnique('mysql.users')->ignore(request()->user()->id),
    ]
]);

echo $validator->fails(); // true
```

> **[Warning]** **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

## Database Blueprint helper

If you're creating your database from the ground up, you don't need to manually create the RUT columns. Just use the `rut()` or `rutNullable()` helpers in the Blueprint:

```php
Schema::create('users', function (Blueprint $table) {
    $table->rut();
    
    // ...
});

Schema::create('company', function (Blueprint $table) {
    $table->rutNullable();
    
    // ...
});
```

> The `rutNullable()` method creates both Number and Verification Digit columns as nullable.

If you plan to use the Number as an index, which may speed up queries to look for RUTs, you can just index the Number column by fluently adding `primary()`, `index()` or `unique()` depending on your database needs. This is because it has more sense to index only the Number rather than the whole RUT.

## RUT trait for Models

This package contains two traits: `HasRut` and `RoutesRut`.

> To use these traits, ensure your model saves the RUT Number and RUT Verification digit in separate columns.

### `HasRut`

This trait conveniently adds a RUT Scope to a model that has a RUT in its columns, and the `rut` property which returns a `Rut` instance.

```php
<?php

namespace App\Models;

use DarkGhostHunter\Lararut\HasRut;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRut;
    
    // ...
}
```

With that, you will have access to convenient RUT queries shorthands:

* `findRut()`: Finds a record by the given RUT.
* `findManyRut()`: Finds many records by the given RUTs.
* `findRutOrFail()`: Finds a record by the RUT or fails.
* `findRutOrNew()`: Finds a record by the RUT or creates one.
* `whereRut()`: Creates a `WHERE` clause with the RUT number equal to the issued one.
* `orWhereRut()`: Creates a `OR WHERE` clause with the RUT number equal to the issued one.

> These RUT queries work over the RUT Number for convenience, as the RUT Verification Digit should be verified on persistence.

The `rut` property is dynamically created from the RUT Number and RUT Verification Digit columns, which uses a caster underneath.

```php
echo $user->rut; // "20490006-K"
```

#### Configuring the RUT columns

By convention, the trait uses `rut_num` and `rut_vd` as the default columns to retrieve and save the RUT Number and RUT Verification Digit, respectively.

You can easily change it to anything your database is working with:

```php
class User extends Authenticatable
{
    use HasRut;
    
    protected const RUT_NUM = 'numero_rut';
    protected const RUT_VD = 'digit_rut';
    
    // ...    
}
```

### `RoutesRut`

You can use the `RoutesRut` trait to override the `resolveRouteBinding()` method of your Eloquent Model to look for its RUT if the field to identify is `rut`.

> Ensure you also add the `HasRut` trait, as it will use the added query scopes.

```php
use DarkGhostHunter\Lararut\HasRut;
use DarkGhostHunter\Lararut\RoutesRut;

class User extends Authenticatable
{
    use HasRut;
    use RoutesRut;
}
```

Then, you will be able to use Route Model Binding in your routes by issuing the `rut` as the name of the field to use to retrieve the model instance from the database.

```php
Route::get('usuario/{user:rut}', function (User $user) {
    return $user;
});
```

> If the RUT is invalid, the model won't be found, so there is no need to validate the rut while route-binding.

## Rut Collection

This package registers a callback to retrieve an array of RUTs as a Laravel Collection when using `many()` and `manyOrThrow()`.

```php
$ruts = Rut::many([
    '15500342-1',
    '7276742-K'
]);

echo $ruts->first(); // "15.500.342-1"
```

## Helpers

This package [includes the `rut()` global helper](https://github.com/DarkGhostHunter/RutUtils/#global-helper) file, which allows you to create a Rut instance anywhere in your code, or a Rut Generator if you don't issue any parameter.

```php
<?php

namespace App\Http\Listeners;

use Illuminate\Auth\Events\Lockout;
use App\Notifications\ProbablyForgotHisPassword;
use App\Notifications\SupportReadyToHelp;
use App\Models\User;

class LogFailedAttempt
{
    /**
     * Handle the event.
     *
     * @param  Lockout  $event
     * @return void
     */
    public function handle(Lockout $event)
    {
        // Get the RUT from the request input
        $rut = rut($event->request->input('rut'));
        
        // If the user who tried exists in the database, notify him.
        if ($user = User::where('rut_num', $rut->num)->first()) {
            $user->notify(new ProbablyForgotHisPassword());
        }
    }
    
    /**
     * Creates many RUTs. 
     * 
     * @return array|\DarkGhostHunter\RutUtils\Rut
     */
    public function generateRuts()
    {
        return rut()->generate(100);
    }
}
```

## License

This package is licenced by the [MIT License](LICENSE).