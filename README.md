# [Liberu CMS](https://www.liberu.org.uk) ![Open Source Love](https://img.shields.io/badge/Open%20Source-%E2%9D%A4-red.svg)

![](https://img.shields.io/badge/PHP-8.4-informational?style=flat&logo=php&color=4f5b93)
![](https://img.shields.io/badge/Laravel-12-informational?style=flat&logo=laravel&color=ef3b2d)
![](https://img.shields.io/badge/Filament-4.0-informational?style=flat&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48cGF0aCBkPSJNMCAwaDQ4djQ4SDBWMHoiIGZpbGw9IiNmNGIyNWUiLz48cGF0aCBkPSJNMjggN2wtMSA2LTMuNDM3LjgxM0wyMCAxNWwtMSAzaDZ2NWgtN2wtMyAxOEg4Yy41MTUtNS44NTMgMS40NTQtMTEuMzMgMy0xN0g4di01bDUtMSAuMjUtMy4yNUMxNCAxMSAxNCAxMSAxNS40MzggOC41NjMgMTkuNDI5IDYuMTI4IDIzLjQ0MiA2LjY4NyAyOCA3eiIgZmlsbD0iIzI4MjQxZSIvPjxwYXRoIGQ9Ik0zMCAxOGg0YzIuMjMzIDUuMzM0IDIuMjMzIDUuMzM0IDEuMTI1IDguNUwzNCAyOWMtLjE2OCAzLjIwOS0uMTY4IDMuMjA5IDAgNmwtMiAxIDEgM2gtNXYyaC0yYy44NzUtNy42MjUuODc1LTcuNjI1IDItMTFoMnYtMmgtMnYtMmwyLTF2LTQtM3oiIGZpbGw9IiMyYTIwMTIiLz48cGF0aCBkPSJNMzUuNTYzIDYuODEzQzM4IDcgMzggNyAzOSA4Yy4xODggMi40MzguMTg4IDIuNDM4IDAgNWwtMiAyYy0yLjYyNS0uMzc1LTIuNjI1LS4zNzUtNS0xLS42MjUtMi4zNzUtLjYyNS0yLjM3NS0xLTUgMi0yIDItMiA0LjU2My0yLjE4N3oiIGZpbGw9IiM0MDM5MzEiLz48cGF0aCBkPSJNMzAgMThoNGMyLjA1NSA1LjMxOSAyLjA1NSA1LjMxOSAxLjgxMyA4LjMxM0wzNSAyOGwtMyAxdi0ybC00IDF2LTJsMi0xdi00LTN6IiBmaWxsPSIjMzEyODFlIi8+PHBhdGggZD0iTTI5IDI3aDN2MmgydjJoLTJ2MmwtNC0xdi0yaDJsLTEtM3oiIGZpbGw9IiMxNTEzMTAiLz48cGF0aCBkPSJNMzAgMThoNHYzaC0ydjJsLTMgMSAxLTZ6IiBmaWxsPSIjNjA0YjMyIi8+PC9zdmc+&&color=fdae4b&link=https://filamentphp.com)
![Jetstream](https://img.shields.io/badge/Jetstream-5-purple.svg)
![Socialite](https://img.shields.io/badge/Socialite-latest-brightgreen.svg)
![](https://img.shields.io/badge/Livewire-3.5-informational?style=flat&logo=Livewire&color=fb70a9)
![](https://img.shields.io/badge/JavaScript-ECMA2020-informational?style=flat&logo=JavaScript&color=F7DF1E)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[![Install](https://github.com/liberu-cms/cms-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-cms/cms-laravel/actions/workflows/install.yml)
[![Tests](https://github.com/liberu-cms/cms-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-cms/cms-laravel/actions/workflows/tests.yml)
[![Docker](https://github.com/liberu-cms/cms-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-cms/cms-laravel/actions/workflows/main.yml)


# [Managed web hosting for our application packages](https://liberu.co.uk)


## Our Projects

* https://github.com/liberu-accounting/accounting-laravel
* https://github.com/liberu-automation/automation-laravel
* https://github.com/liberu-billing/billing-laravel
* https://github.com/liberusoftware/boilerplate
* https://github.com/liberu-browser-game/browser-game-laravel
* https://github.com/liberu-cms/cms-laravel
* https://github.com/liberu-control-panel/control-panel-laravel
* https://github.com/liberu-crm/crm-laravel
* https://github.com/liberu-ecommerce/ecommerce-laravel
* https://github.com/liberu-genealogy/genealogy-laravel
* https://github.com/liberu-maintenance/maintenance-laravel
* https://github.com/liberu-real-estate/real-estate-laravel
* https://github.com/liberu-social-network/social-network-laravel


## Setup

1. Ensure your environment is set up with PHP 8.3 and Composer installed.
2. Download the project files from this GitHub repository.
3. Open a terminal in the project folder. If you are on Windows and have Git Bash installed, you can use it for the following steps.
4. Run the following command:

```bash
./setup.sh
```

and everything should be installed automatically if you are using Linux you just run the script as you normally run scripts in the terminal.

NOTE 1: The script will ask you if you want to have your .env be overwritten by .env.example, in case you have already an .env configuration available please answer with "n" (No).

NOTE 2: This script will run seeders, please make sure you are aware of this and don't run this script if you don't want this to happen.
```bash
composer install
php artisan key:generate
php artisan migrate --seed
```
This will install the necessary dependencies, generate an application key, and set up your database with initial data.

NOTE 3: Ensure your `.env` file is correctly configured with your database connection details before running migrations.

## Building with Docker

Alternatively, you can build and run the project using Docker. To build the Dockerfile, follow these steps:

1. Ensure you have Docker installed on your system.
2. Open a terminal in the project folder.
3. Run the following command to build the Docker image:
   ```
   docker build -t cms-laravel .
   ```
4. Once the image is built, you can run the container with:
   ```
   docker run -p 8000:8000 cms-laravel
   ```

NOTE 3: Ensure your `.env` file is correctly configured with your database connection details before running migrations.

### Using Laravel Sail

This project also includes support for Laravel Sail, which provides a Docker-based development environment. To use Laravel Sail, follow these steps:

1. Ensure you have Docker installed on your system.
2. Open a terminal in the project folder.
3. Run the following command to start the Laravel Sail environment:
   ```
   ./vendor/bin/sail up
   ```
4. Once the containers are running, you can access the application at `http://localhost`.
5. To stop the Sail environment, press `Ctrl+C` in the terminal.

For more information on using Laravel Sail, refer to the [official documentation](https://laravel.com/docs/sail).


### Description
Welcome to Liberu CMS, our forward-thinking open-source project designed to empower content creators and administrators alike. Leveraging the dynamic capabilities of Laravel 11, PHP 8.3, Livewire 3, and Filament 3, Liberu CMS is not just a content management system – it's a versatile and intuitive platform crafted to elevate the creation, organization, and delivery of digital content.

**Key Features:**

1. **User-Friendly Content Creation:** Liberu CMS provides an intuitive and user-friendly interface for content creation. From articles and multimedia to dynamic pages, our project ensures that content creators can bring their ideas to life with ease.

2. **Dynamic Livewire Interactions:** Built on Laravel 11 and PHP 8.3, Liberu CMS integrates Livewire 3 to deliver dynamic and real-time interactions. Enjoy seamless and responsive user experiences as you edit, preview, and publish content without the need for page refreshes.

3. **Efficient Admin Panel:** Filament 3, our admin panel built on Laravel, adds an extra layer of efficiency to Liberu CMS. Administrators can manage users, customize settings, and oversee the entire content ecosystem with a powerful and intuitive interface.

4. **Customizable Templates:** Tailor your website's appearance with customizable templates. Liberu CMS offers flexibility in design, allowing users to create unique and visually appealing websites that align with their brand and vision.

5. **Scalability and Performance:** Whether you're managing a personal blog or a large-scale enterprise website, Liberu CMS is built for scalability and optimized performance. The project adapts to your content needs, ensuring a seamless experience for both creators and visitors.

Liberu CMS is open source, released under the permissive MIT license. We invite developers, content creators, and organizations to contribute to the evolution of content management systems. Together, let's redefine the standards of digital content creation and management.

Welcome to Liberu CMS – where innovation meets simplicity, and the possibilities of digital content creation are limitless. Join us on this journey to transform the way we create, manage, and deliver content to the world.

### Licensed under MIT, use for any personal or commercial project.
  
### Contributions

We warmly welcome new contributions from the community! We believe in the power of collaboration and appreciate any involvement you'd like to have in improving our project. Whether you prefer submitting pull requests with code enhancements or raising issues to help us identify areas of improvement, we value your participation.

If you have code changes or feature enhancements to propose, pull requests are a fantastic way to share your ideas with us. We encourage you to fork the project, make the necessary modifications, and submit a pull request for our review. Our team will diligently review your changes and work together with you to ensure the highest quality outcome.

However, we understand that not everyone is comfortable with submitting code directly. If you come across any issues or have suggestions for improvement, we greatly appreciate your input. By raising an issue, you provide valuable insights that help us identify and address potential problems or opportunities for growth.

Whether through pull requests or issues, your contributions play a vital role in making our project even better. We believe in fostering an inclusive and collaborative environment where everyone's ideas are valued and respected.

We look forward to your involvement, and together, we can create a vibrant and thriving project. Thank you for considering contributing to our community!
<!--/h-->

### License

This project is licensed under the MIT license, granting you the freedom to utilize it for both personal and commercial projects. The MIT license ensures that you have the flexibility to adapt, modify, and distribute the project as per your needs. Feel free to incorporate it into your own ventures, whether they are personal endeavors or part of a larger commercial undertaking. The permissive nature of the MIT license empowers you to leverage this project without any unnecessary restrictions. Enjoy the benefits of this open and accessible license as you embark on your creative and entrepreneurial pursuits.
<!--/h-->

## Contributors


<a href = "https://github.com/liberu-cms/cms-laravel/graphs/contributors">
  <img src = "https://contrib.rocks/image?repo=liberu-cms/cms-laravel"/>
