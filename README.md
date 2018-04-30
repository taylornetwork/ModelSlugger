# ModelSlugger

## Install

Using Composer

```bash
$ composer require taylornetwork/model-slugger
```

## Usage

In a class that extends `Illuminate\Database\Eloquent\Model` add the `TaylorNetwork\ModelSlugger\ModelSlugger` trait.

This will require you to define the `sluggerConfig()` function which returns an array with minimum options being `['source' => 'model field to make slug from']`

```php
// app/ExampleModel.php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TaylorNetwork\ModelSlugger\ModelSlugger;

class ExampleModel extends Model
{
	use ModelSlugger;

	public function sluggerConfig()
	{
		return [ 
			'source' => 'name',
		];
	}
}

```

Where `name` would be converted to a slug and placed into the column `slug` by default

## Bind slug to route

You can bind the routes in your application to the slug rather than ID by adding

```php
protected $sluggerRouteModelBind = true;
```

To your model, it will cause the routes to be looked up using the slug column.

## Unique

To make all slugs unique add `'unique' => 'all'` to the config array either in the model or in `config/slugger.php`

```php
// app/ExampleModel.php

public function sluggerConfig()
{
  return [
    'source' => 'name',
    'unique' => 'all',
  ];
}
```

## Unique to a Parent 

If you want slugs to only be unique based on a parent class, add `'unique' => 'parent', 'parent' => 'App\ParentClassName'` to the model or `config/slugger.php`

For example you have a `App\User` model and a `App\TodoList` model where the a user can have many todo lists each with slugs. If we set the config to `unique => all` and every user makes a todo list named `'my todo list'` the slugs will become increasingly long as they become unique. 

To avoid this you can make the slugs unique if they are from the same parent.

```php
// app/TodoList.php

public function sluggerConfig()
{
  return [
    'source' => 'name',
    'unique' => 'parent',
    'parent' => 'App\User',
  ];
}
```

### Slugger Route Model Bind with Unique to a Parent

To accomplish route model binding you will need to add the code to find only slugs with the correct parent to your `App\Providers\RouteServiceProvider` class

For example

```php
public function boot()
{
    Route::bind('user', function ($value) {
        return App\User::findOrFail($value); // Code to find by ID or slug
    });

    Route::bind('todoList', function ($slug, $route) {
        // $route->parameter('user') will return an instance of App\User 
        return $route->parameter('user')->todoLists()->where('slug', $slug)->firstOrFail(); 
    });

    parent::boot();
}
```

## Credits

- Main Author: [Sam Taylor][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/taylornetwork
