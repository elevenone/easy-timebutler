<p align="center">
    <img src="https://bloatless.org/img/logo.svg" width="60px" height="80px">
</p>

<h1 align="center">Bloatless Endocore App</h1>

<p align="center">
    A boilerplate application for the Endocore framework.
</p>

You can use this bootstrap application to quickly start a new project based on the
[Endocore framework](https://github.com/bloatless/endocore).

This repository includes all required files and samples so you can start building your project right away.

## Installation

The easiest way to create a new Endocore application is by using composer. In the directory where you
want to start your project execute the following command:

```bash
php composer.phar create-project bloatless/endocore_app my_project_name
```

You can of course change `my_project_name` to whatever project name you want.

After that you can change into the just created project folder and start a PHPs webserver to test
if the application was installed correctly:

```bash
cd my_project_name
php -S localhost:8080 -t public public/index.php
```

You can now access your application by pointing your browser to `http://localhost:8080`.

Of course you can also use any other webserver like nginx or apache. Just point the document root
of your projects vhost to the `public` folder inside your project folder.

## Documentation

The Endocore app sourcecode includes some useful examples and includes inline documentation wherever necessary.

Additionally there is a complete documentation on the
[Endocore framework GitHub page](https://github.com/bloatless/endocore). 

## License

MIT
