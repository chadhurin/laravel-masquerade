# Laravel Masquerade

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chadhurin/laravel-masquerade.svg?style=flat-square)](https://packagist.org/packages/chadhurin/laravel-masquerade)
[![Total Downloads](https://img.shields.io/packagist/dt/chadhurin/laravel-masquerade.svg?style=flat-square)](https://packagist.org/packages/chadhurin/laravel-masquerade)

Elevate your user management experience with the powerful Laravel Masquerade Package. Designed for seamless integration with Laravel applications, this package empowers administrators to temporarily switch and view the application through the eyes of another user. Whether you're debugging, testing user experiences, or verifying permissions, our Masquerade Package streamlines the process.

## Key Features

- **Effortless User Switching:** Quickly and securely switch to any user account within your application.
- **Realistic User Experience:** Gain insights by experiencing the application exactly as the selected user would.
- **Advanced Permissions Testing:** Verify and troubleshoot user permissions with ease.
- **Developer-Friendly:** Intuitive setup and configuration for smooth integration into your existing Laravel project.

## Inspiration

This package was inspired by the fantastic work done in the [laravel-impersonate](https://github.com/404labfr/laravel-impersonate) package.
We recognized the value it brought to the community and aimed to build upon its ideas while adding our unique features particularly with Laravel Sanctum support. We needed to quickly add support for Sanctum so a few other features were left out. We hope to add them in the future.


## Requirements

- Laravel 10.x
- PHP >= 8.0

## Installation

You can install the package via composer:

```bash
composer require chadhurin/laravel-masquerade
```

Then, add the trait `Chadhurin\LaravelMasquerade\Traits\Masquerade` to your **User** model.
# Usage

## Simple usage

Masquerade a user:
```php
Auth::user()->masquerade($other_user);
// You're now logged as the $other_user
```

Leave a Masquerade:
```php
Auth::user()->leaveMasquerade();
// You're now logged as your original user.
```

### Using the built-in controller

In your routes file, under web middleware, you must call the `masquerade` route macro.

```php
Route::masquerade();
```


## Advanced Usage

### Defining masquerade authorization


By default all users can **masquerade** an user.  
You need to add the method `canMasquerade()` to your user model:

```php
    /**
     * @return bool
     */
    public function canMasquerade(): bool
    {
        return $this->hasRole('super-admin');
        // For example, you can check if the user has a specific role.
    }
```
By default all users can **be masqueraded**.  
You need to add the method `canBeMasqueraded()` to your user model to extend this behavior:

```php
    /**
     * @return bool
     */
     public function canBeMasqueraded():bool
    {
        return !$this->hasRole('super-admin')
        && $this->can_be_masqueraded;
        // For example, you can check if the user has a specific role and if the user has a flag on the users table
    }
```

## Configuration

The package comes with a configuration file.

Publish it with the following command:
```bash
php artisan vendor:publish --tag=masquerade
```


Available options:
```php
'take_redirect_to' => '/',
'leave_redirect_to' => '/'
```


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email chadhurin@gmail.com instead of using the issue tracker.

## Credits

-   [Chad Hurin](https://github.com/dhadhurin)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
