<p align="center"><img src="https://cms.snappyshopper.co.uk/uploads/logo_Mobile_74df0bc87e.svg" width="400" alt="Snappy Shopper"></a></p>

## Dependencies

-   php 8.3.6
    -   php-cli
    -   php-common
    -   php-opcache
    -   php-readline
    -   php-zip
    -   php-mbstring
    -   php-xml
    -   php-sqlite3
-   composer 2.8.5
-   node 18.19.1
-   npm 9.2.0
-   unzip 6.0.0

## Installation

```sh
git clone https://github.com/CRISvsGAME/snappy.git
cd snappy
npm run build
php artisan migrate
composer run dev
```

## import:postcodes

The `import:postcodes` command is defined in the file located at:

```php
App\Console\Commands\ImportPostcodes
```

You can view all available options by running:

```sh
php artisan import:postcodes --help
```

## API Endpoints

-   GET /api/stores – List all stores.

```sh
#Example:
curl -X GET http://localhost:8000/api/stores \
  -H "Content-Type: application/json"
```

-   POST /api/stores – Create a new store.

```sh
# Example:
curl -X POST http://localhost:8000/api/stores \
  -H "Content-Type: application/json" \
  -d '{"name": "Store 1", "lat": 40.712776, "long": -74.005974, "is_open": true, "store_type": "Grocery", "max_delivery_distance": 15}'
```

-   PUT/PATCH /api/stores/{store} – Update a store.

```sh
# Example:
curl -X PUT http://localhost:8000/api/stores/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "Different Name"}'
```

DELETE /api/stores/{store} – Delete a store.

```sh
# Example:
curl -X DELETE http://localhost:8000/api/stores/1 \
  -H "Content-Type: application/json"
```

## Models

```php
App\Models\Postcode
App\Models\Store
```

## Controllers

```php
App\Http\Controllers\Api\StoreController
```

## Middlewares

```php
App\Http\Middleware\EnsureJsonResponse
```

## Improvements

### `import:postcodes`

-   Thorough validation
-   Flexibility and configuration

### Database

-   Production database like MySQL or PostgreSQL to enforce data types

### Models

-   Mutators and Accessors to set and retrieve data

### Migrations

-   Use of more accurate data types for columns

### Requests

-   Creating form request classes for data validation for separation of concerns

## Known Issues

-   SQLite doesn't enforce data types
-   The code has not been taken through refactoring
-   Inline documentation has not been added for brevity

## License

This repository is a temporary dummy project. [License](https://crisvsgame.com/license).

The logo is Copyright of [Snappy Shopper](https://www.snappyshopper.co.uk/).
