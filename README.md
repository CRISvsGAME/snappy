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

```sh
php artisan import:postcodes --help
```

## Improvements

### `import:postcodes`

-   Thorough validation
-   Flexibility and configuration

## License

This repository is a temporary dummy project. [License](https://crisvsgame.com/license).

The logo is Copyright of [Snappy Shopper](https://www.snappyshopper.co.uk/).
