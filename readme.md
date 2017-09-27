# Super Spinner

Based on http://www.paul-norman.co.uk/2010/09/php-spinner-updated-spin-articles-for-seo.

## Install

```
composer require ferri/spinner
```

## Usage

```php
<?php

$string = '{The|A} {quick|speedy|fast} {brown|black|red} {fox|wolf} {jumped|bounded|hopped|skipped} over the {lazy|tired} {dog|hound}';

echo Ferri\Spinner::detect($string);
```