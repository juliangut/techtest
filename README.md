[![PHP version](https://img.shields.io/badge/PHP-%3E%3D5.6-8892BF.svg?style=flat-square)](http://php.net)
[![License](https://img.shields.io/github/license/juliangut/techtest.svg?style=flat-square)](https://github.com/techtest/blob/master/LICENSE)

# TechTest

Technical test

## Requirements

* PHP >= 5.6
* ext-pdo_sqlite
* Composer
* nodejs (for tooling)
* npm (for tooling)

## Usage

### Installation

Clone repository

```
git clone git@github.com:juliangut/techtest.git
```

Install tooling dependencies

```
npm install -g gulp bower
```

Install project dependencies and tooling

```
composer install
npm install
bower install
```

### Running

```
gulp serve
```

Or you can run the server manually

```
php -S localhost:9000 -t public
```

Any way will start the PHP built-in server at `http://localhost:9000`

#### Available users

The application comes with 4 users bundled

* Creator of C language "Dennis Ritchie", user: `Dennis`, password: `Ritchie`, role: `PAGE_1`
* Creator of PHP language "Rasmus Lerdorf", user: `Rasmus`, password: `Lerdorf`, role: `PAGE_2`
* Creator of JavaScript language "Brendan Eich", user: `Brendan`, password: `Eich`, role: `PAGE_3`
* Creator of all things and beyond "Richard Stallman", user: `Richard`, password: `Stallman`, role: `ADMIN`

> Be aware that user's name IS the user identifier and is CASE SENSITIVE for any operation

> Each user has only one role, but more than one can be configured 

To ease HTTP basic authentication base64 encoded credentials are listed below

* Dennis Ritchie: `RGVubmlzOlJpdGNoaWU=`
* Rasmus Lerdorf: `UmFzbXVzOkxlcmRvcmY=`
* Brendan Eich: `QnJlbmRhbjpFaWNo`
* Richard Stallman: `UmljaGFyZDpTdGFsbG1hbg==`

#### Frontend

Just browse to `http://localhost:9000`

#### API

For Content Negotiation to take effect `Accept` header must be provided. If not provided `application/json` will be the default content type

> All the examples use user "Richard Stallman" as example as it is the one with "ADMIN" role

Lets see the usage by a complete execution flow, request all users

##### GET `/api/users`

```
curl -X GET -H "Host: localhost:9000" -H "Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==" -H "Accept: application/json" "http://localhost:9000/api/users"
```

###### Result

A list of registered users

```
[
  {
    "username": "Richard",
    "roles": [
      "ADMIN"
    ]
  },
  {
    "username": "Dennis",
    "roles": [
      "PAGE_1"
    ]
  },
  {
    "username": "Rasmus",
    "roles": [
      "PAGE_2"
    ]
  },
  {
    "username": "Brendan",
    "roles": [
      "PAGE_3"
    ]
  }
]
```

##### POST `/api/user`

```
curl -X POST -H "Host: localhost:9000" -H "Content-Type: application/json" -H "Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==" -H "Accept: application/json" -d '{
   "username": "Julian",
   "password": "Gutierrez",
   "roles": "PAGE_3"
}' "http://localhost:9000/api/user"
```

###### Result

The newly created user

```
{
  "username": "Julian",
  "roles": [
    "PAGE_3"
  ]
}
```

##### GET `/api/user/Julian`

A single user

```
curl -X GET -H "Host: localhost:9000" -H "Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==" -H "Accept: application/json" "http://localhost:9000/api/user/Julian"
```

###### Result

```
{
  "username": "Julian",
  "roles": [
    "PAGE_3"
  ]
}
```

##### PUT `/api/user/Julian`

```
curl -X PUT -H "Host: localhost:9000" -H "Content-Type: application/json" -H "Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==" -H "Accept: application/json" -d '{
    "password": "Tejada",
    "roles": "PAGE_1,PAGE_3"
}' "http://localhost:9000/api/user/Julian"
```

###### Result

The user with updated data. Mind that password is changed but not displayed

```
{
  "username": "Julian",
  "roles": [
    "PAGE_1",
    "PAGE_3"
  ]
}
```

##### DELETE `/api/user/Julian`

```
curl -X DELETE -H "Host: localhost:9000" -H "Authorization: Basic UmljaGFyZDpTdGFsbG1hbg==" -H "Accept: application/json" "http://localhost:9000/api/user/Julian"
```

###### Result

The user just being removed

```
{
  "username": "Julian",
  "roles": [
    "PAGE_1",
    "PAGE_3"
  ]
}
```


## Architecture

I've attached to DRY principle trying to adhere to the constraints of the task

Also during the whole project Composition over Inheritance has been promoted as much as possible

### MVC architecture

Separation of Model and Controller is clear, never the less due to the simple nature of project View is treated differently and not completely disconnected from Controller in two different ways
 
For JSON and XML content response body is constructed of the fly with the data provided by the controllers, there is no need for a view layer

HTML content is generated inside the controller (route callback) and provided back to the system. [Twig](http://twig.sensiolabs.org), [Plates](http://platesphp.com) or any other View renderer could be included to handle this in the Dependency Injection Container, injected and used by changing one line in each route callback

### Front controller

`/public/index.php` is the front controller for the whole project and is where the built-in server points when launched with `gulp serve`

The front controller is responsible only for bootstrapping the application, configuring Dependency Injection Container and setting routes and middleware

### Dependency Injection Container

[PHP-DI](http://php-di.org) is used as the DIC for the project. The definitions for the different services provided by the container are logically separated into PHP files in `/path/to/project/config`

**Note** that aside from configurations and services, the route callbacks are also defined in this path as they are retrieved from the DIC when needed

### Model

To store model data a `sqlite` database is automatically created (`/path/to/project/config/db.sqlite3`) and populated on first run

The responsibility of querying the database is centralized on `UserRepository` which in the end is just a thin wrapper over the PDO object provided to it

> I've not used an ORM such as Doctrine as it would be overkill in this case

### HTTP

> All here expressed is HARD based on the constraint NOT to use implementations of `Symfony/HttpKernel` or `Symfony/HttpFoundation` and, as I've understand, for extension implementations of `PSR7 Message Interface`

HTTP interaction is handled by a fairly simple HTTP abstraction covering Request and Response objects with just the exact number of methods needed to cover the technical test needs. So considerations about more advanced topics such as uploaded files or memory overflow on response body length are left aside
 
This HTTP abstraction has been loosely inspired by PSR7 interfaces. Anyway as simple as it is it leaves out the most important parts of PSR7, namely object immutability, even though when handling middleware the objects are treated AS IF they where immutable when they are not 

### Routing

Routing is handled by wrapping [FastRoute](https://github.com/nikic/FastRoute) router to easy discovering AND allow routes middleware on dispatching

The router could have been decoupled from the main Application but the actual dispatching is taken care of in just 30 lines long

### Middleware

This is the most interesting part. A middleware architecture similar to [PHP Stack](http://stackphp.com/) has been introduced but in a way simpler manner

It is present at two levels: 1 Top most Application which is applied first, and 2 Route middleware, applied before route callback (Controller)

This architecture allows to defer most of the required work to a smart combination of middleware:

* `Session` handles session creation and sets session cookie in Response object at the end of the execution instead of relying on PHP default mechanisms
* `Negotiation` handles Content Negotiation by using [Negotiation](http://williamdurand.fr/Negotiation/) package. Extracts information from Request object and stores it in Response object so the final piece of the architecture (response display) can act accordingly
* `SessionAuth` and `HttpAuth` both handle user authentication, first one by checking session variables and the later by verifying HTTP basic authentication
* `ACL` is a wrapper over [Zend Framework Permissions ACL](https://zendframework.github.io/zend-permissions-acl/) that simplifies the control access to route callbacks to the proper roles, based on path (and method where needed)

### Error handling

Errors are handled by extending Exceptions. There is one exception per possible HTTP error (400, 401, 403, 404 and 405) that are thrown on the correct moment by the system itself, by any middleware or by route callbacks themselves. Additionally there is an extra 500 Exception handler to handle all possible not already catch Exceptions

This mechanism allows the different parts of the system to raise the corresponding Exception (according to the HTTP error) or continue with execution. Additionally as this errors ar captured at top application level the error is treated and the corresponding error-aware Response object is reintroduced at the end of the flow so the display mechanism treats them as "normal" returning content

## Improvements

### HTTP

Biggest area of improvement. Abstraction can be vastly improved BUT of course it is better not to waste time in this and use a PSR7 implementation instead and gain huge benefits with the move

### Route grouping

It is not possible to group routes at this moment and this poses a problem when attaching middleware to routes, applying the same middleware to several routes means repeating the same line several times

### Exception handling

There is way of improvement on Exceptions content negotiation by using the same mechanism as the rest of the project. This is not possible right now because route matching is done prior to middleware traversing thus not allowing the middleware to run when some error occur in this first stage (such as 404, 405, and some 500)

Also 500 errors are just PHP execution errors, not so pretty

### Validation

Content validation is not taking place in any form. This means that POST and PUT parameters are not being validated in any way so the validation of that data (format, key constraints, uniqueness, ...) is delegated to the database (possible SQLSTATE[xxxxx] errors)

### Caching

There is no point in adding caching in this technical test but doing so (in middleware) would benefit execution and response times

## Tooling

Running tools for QA

```
gulp
```

The following tools will be run

* php -l
* PHPUnit
* PHP CodeSniffer
* PHP Mess Detector
* PHP Copy/Paste Detector

> PHPUnit coverage report will is stored at `/path/to/project/dist/coverage`

Additionally to check for outdated dependencies run

```
gulp security
```

> All the code is docblock annotated so using by using PHPDocumentor an API documentation can be easily generated

## License

See file [LICENSE](https://github.com/juliangut/TechTest/blob/master/LICENSE) included with the source code for a copy of the license terms.
