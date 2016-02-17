# Contributing

Contributions are **welcome**!

We accept contributions via Pull Requests on [Github](https://github.com/graze/:package-name). We also recommend reading [How to write the perfect Pull Request](https://github.com/blog/1943-how-to-write-the-perfect-pull-request) which has some great tips and advice.

## Reporting an Issue

Please report issues via the issue tracker on GitHub. For security-related issues, please email the maintainer directly.

## Pull Requests

Contributions are accepted via Pull Requests. In order for your Pull Request to be merged, please ensure it meets
the following criteria:

- **PSR-2 & PSR-4 Coding Standards**.
- **Tests** - your contribution will not be merged unless it has tests covering the changes.
- **Documentation** - please ensure that README.md and any other documentation relevant to your change is up-to-date.
- **Description** - please provide a description of your pull request that details the changes you've made, why you've
made them including any relevant information or justifications that will aid the person reviewing you changes.

## Development Environment

If you need a vagrant box for development, we recommend using [adlawson/vagrantfiles](https://github.com/adlawson/vagrantfiles), for PHP:

```shell
$ curl -O https://raw.githubusercontent.com/adlawson/vagrantfiles/master/php/Vagrantfile
$ vagrant up
$ vagrant ssh
$ cd /srv
```

## Running Tests

You can run all of the test suites in the project using:

```shell
$ make test
```

Or run individual suites using:

```shell
$ make test-unit
$ make test-functional
```

You can get a coverage report in text:

```shell
$ make test-coverage
$ make test-unit-coverage
$ make test-functional-coverage
```

An HTML coverage report can be written to `tests/report` using:

```shell
$ make test-coverage-html
$ make test-unit-coverage-html
$ make test-functional-coverage-html
```

For a coverage report in clover XML format use:

```shell
$ make test-coverage-clover
$ make test-unit-coverage-clover
$ make test-functional-coverage-clover
```
