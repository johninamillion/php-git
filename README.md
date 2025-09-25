# PHP Git

[![PHPUnit](https://github.com/johninamillion/php-git/actions/workflows/phpunit.yml/badge.svg)](https://github.com/johninamillion/php-git/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/johninamillion/php-git/actions/workflows/phpstan.yml/badge.svg)](https://github.com/johninamillion/php-git/actions/workflows/phpstan.yml)

---

## Table of Contents

- [Installation](#installation)
- [Customization](#customization)
- [Development](#development)
- [License](#license)

---

## Installation

You can install the package via Composer:

```bash
composer require --dev johninamillion/php-git
```

## Development

### Analyze

To analyze your code for potential issues, you can run [phpstan](https://github.com/phpstan/phpstan):

```bash
composer code:analyse
```

### CS-Fixer

To ensure your code adheres to the coding standards, you can run the [php-cs-fixer](https://github.com/php-cs-fixer/php-cs-fixer).

```bash
composer code:format
```

### Testing

To run the tests, make sure you have installed [phpunit](https://github.com/sebastianbergmann/phpunit) within the dev dependencies and then run:

```bash
composer test
```

Check the Test Coverage:

```bash
composer test:coverage
```

---

## License
This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

---

<div style="text-align: center">All Glory To God - The Father, The Son, and The Holy Spirit.</div><br>
