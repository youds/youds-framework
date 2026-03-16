# Youds Framework

Youds Framework is a high-performance, modular PHP framework designed for building robust and scalable web applications. Based on the proven architecture of Mojavi and Agavi, it provides a flexible environment with a focus on configuration-driven development.

## Features

-   **Modular Architecture**: Easily organize your application into logical modules.
-   **Flexible Configuration**: Comprehensive configuration system using XML or PHP.
-   **Autoloading**: Integrated autoloader for seamless class management.
-   **Database Integration**: Support for multiple database backends, including integration with Illuminate Database.
-   **Testing Support**: Built-in testing framework with PHPUnit integration.
-   **Developer Bar**: Handy debugging and profiling tool for development.
-   **Advanced Routing**: Powerful routing system to handle complex URL structures.
-   **Validation & Filtering**: Robust input validation and data filtering.
-   **Localization**: Multi-language support with CLDR data.

## Requirements

-   PHP 5.3 or greater.

## Installation

You can include Youds Framework in your project by cloning the repository or using Composer (if configured).

```bash
git clone https://github.com/your-repo/youds-framework.git
```

## Quick Start

To bootstrap the framework in your application:

```php
<?php

require_once 'path/to/youds-framework/youds-framework.php';

use YoudsFramework\YoudsFramework;

// Bootstrap the framework with a specific environment (e.g., 'dev', 'prod')
YoudsFramework::bootstrap('dev');

// Your application logic here
```

## Directory Structure

-   `action/`: Application actions and logic.
-   `config/`: Configuration files and loaders.
-   `controller/`: Request handling and output management.
-   `core/`: Core framework classes.
-   `database/`: Database abstraction and drivers.
-   `layout/`: View layouts and rendering.
-   `routing/`: URL routing definitions.
-   `storage/`: Cache and temporary file storage.
-   `testing/`: Testing suite and utilities.
-   `util/`: Utility classes and helpers.

## License

This project is licensed under the terms of the LICENSE file included in the source code. You can also view it online at [http://framework.youds.com/download/LICENSE](http://framework.youds.com/download/LICENSE).

## Authors

-   Sean Kerr (Mojavi)
-   Mike Vincent (Agavi)
-   David Zülke (Agavi)
-   Craig Fairhurst (Youds Framework)

## Links

-   Official Website: [https://framework.youds.com](https://framework.youds.com)
