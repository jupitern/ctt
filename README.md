# jupitern/ctt
#### CTT (Portugal post office) tracking for PHP.

track mail and packages using CTT service (Portuguese post office)
https://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx

## Requirements

PHP 5.6 or higher.

## Installation

Include jupitern/ctt in your project, by adding it to your composer.json file.
```php
{
    "require": {
        "jupitern/ctt": "1.*"
    }
}
```


## Usage
```php
$ctt = new \Jupitern\Ctt\CttTracking();
$res = $ctt->trackObjects(['ED123456789PT', 'LX123456789PT']);
var_dump($res);

/*
output:
Array
(
    [ED123456789PT] => Array
        (
            [status] => 6
            [statusText] => Objeto não encontrado
        )

    [LX123456789PT] => Array
        (
            [status] => 4
            [statusText] => Objeto entregue
        )

)
*/
```

## ChangeLog

 - initial release

## Contributing

 - welcome to discuss a bugs, features and ideas.

## License

jupitern/ctt is release under the MIT license.

You are free to use, modify and distribute this software
