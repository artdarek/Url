# PHP Url Builder

---

- [Installation](#installation)
- [Usage](#usage)

## Installation 

```php

    // include library
        include('src/Artdarek/Url.php');

```

## Usage

```php

    // build url
        $url = (new Artdarek\Url() )->make($_GET)
            ->take(['a','s1','s2','s3','s4','s5'])
            ->base('https://domain.com')
            ->add('test', 'test_value')
            ->combine('combined', ['s1','s2'])
            ->get();

        var_dump($url);

```