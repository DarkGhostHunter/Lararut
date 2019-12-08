![Loïc Mermilliod - Unsplash (UL) #H6KJ2D0LphU](https://images.unsplash.com/photo-1490782300182-697b80ad4293?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1280&h=400&q=80)

[![Latest Stable Version](https://poser.pugx.org/darkghosthunter/lararut/v/stable)](https://packagist.org/packages/darkghosthunter/lararut) [![License](https://poser.pugx.org/darkghosthunter/lararut/license)](https://packagist.org/packages/darkghosthunter/lararut)
![](https://img.shields.io/packagist/php-v/darkghosthunter/lararut.svg) [![Build Status](https://travis-ci.com/DarkGhostHunter/Lararut.svg?branch=master)](https://travis-ci.com/DarkGhostHunter/Lararut) [![Coverage Status](https://coveralls.io/repos/github/DarkGhostHunter/Lararut/badge.svg?branch=master)](https://coveralls.io/github/DarkGhostHunter/Lararut?branch=master) [![Maintainability](https://api.codeclimate.com/v1/badges/b07f8f752242ba1f2831/maintainability)](https://codeclimate.com/github/DarkGhostHunter/Lararut/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/b07f8f752242ba1f2831/test_coverage)](https://codeclimate.com/github/DarkGhostHunter/Lararut/test_coverage)

# Lararut

This package integrates the [RutUtils](https://github.com/DarkGhostHunter/RutUtils/) package, allowing manipulation of RUTs in your PHP project, with Laravel.

Additionally, it includes 4 new rules to validate RUT data conveniently.

> **Important** This package does not validate if the RUT is from a real person, only if its valid. If you need that kind of functionality, you should let your application interact with the [pseudo-official API](https://portal.sidiv.registrocivil.cl/usuarios-portal/pages/DocumentRequestStatus.xhtml).

## Requirements

- PHP 7.2+
- Laravel 5.8 or 6.x (Lumen *may* work)

> Check older releases for older Laravel versions.

## Installation

Fire up Composer and require it into your project:

```bash
composer require darkghosthunter/lararut
```

## Usage

This package offers some utilities using [RutUtils](https://github.com/DarkGhostHunter/RutUtils/):

* Validation Rules
* Type-hinting in controller methods

Check the [RutUtils documentation](https://github.com/DarkGhostHunter/RutUtils/blob/master/README.md) to see all the available methods to create, generate and validate RUTs.

### Helpers

This package also [includes the `rut()` global helper](https://github.com/DarkGhostHunter/RutUtils/#global-helper) file, which allows you to create a Rut instance anywhere in your code.

```php
<?php

namespace App\Http\Listeners;

use Illuminate\Auth\Events\Lockout;
use App\Notifications\ProbablyForgotHisPassword;
use App\Notifications\SupportReadyToHelp;
use App\User;

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
            $user->notify(new ProbablyForgotHisPassword);
        }
    }
}
```

### Validation rules

This package includes handy rules to validate RUTs incoming from your frontend. Compared to prior versions, they're are more easy to use and understand.

> Database rules will automatically normalize `K` verification _digit_ to search in the database.

#### `rut`

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

This may come handy in situations when the user presses a wrong button into an input. Afterwards, you can use `Rut::make()` to create a new Rut instance from that input.

```php
<?php

use \DarkGhostHunter\RutUtils\Rut;

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

#### `rut_strict` 

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

#### `rut_equal` 

This will check if the RUT is equal to another RUT, like for example, the User's RUT or from another data resource. Both will be cleaned before the validation procedure.

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

#### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for RUT Number and Verification Digit.

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

Since this also checks if the RUT is valid, it will return `false` if its not, or the RUT doesn't exists in the database.

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

#### `num_exists` (Database)

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

#### `rut_unique` (Database)

This works the same as the `rut_exists` rule, but instead of checking if the RUT exists in the Database, it will detect if it doesn't. This rule works just like the [Laravel's `unique` rule works](https://laravel.com/docs/5.8/validation#rule-unique).

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
        Rule::rutUnique('mysql.users', 'rut_num')->ignore(request()->user()->id),
    ]
]);

echo $validator->fails(); // true
```

> **[Warning]** **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

#### `num_unique` (Database)

This rule will check only if the **number** of the RUT doesn't exists already in the database, which is useful for Databases with an index solely on the number of the RUT. This rule also matches the [Laravel's `unique` rule works](https://laravel.com/docs/5.8/validation#rule-unique).

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

## License

This package is licenced by the [MIT License](LICENSE).