# Changelog

All important changes to `fiisoft-basics` will be documented in this file

## 2.1.0

Added new static methods to class Date:
- mutable(): DateTime
- object(): DateTimeInterface
- isFirstNotOlderThenSecond(): bool

## 2.0.1

Fixed wrong path to directory with test-coverage report.
File tests/bootstrap.php removed, phpunit-configuration files adjusted.

## 2.0.0

* shift to PHP version 5.6
* upgrade required phpunit version to ^5.7.19
* add phpunit.xml and phpunit-cc.xml files 
* git ignore /tests/coverage
* git ignore Eclipse project's files and folders

## 1.0.0

First version, added classes AbstractConfiguration, Date and SpecificationValidator with tests.
