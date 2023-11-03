# WordPress Plugin

A WordPress plugin template that can be easily installed using Composer.

## Requirements

- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [Composer](https://getcomposer.org/doc/00-intro.md)

## Installation

Run this command in the terminal:
```
composer --remove-vcs create-project wp-forge/wp-plugin
``` 

Optionally append a directory name to the end of the command to customize the folder name that your project will be installed into.

The installation process will check your environment for the required PHP version and PHP extensions. If this presents a problem and you want to force install anyway, just add the `--ignore-platform-reqs` flag to the command.

## Usage



## Reminders

Don't forget to:

- Delete the `composer.json` file and the `vendor` folder.
- Run `composer init` to create a new `composer.json` file for your project.
- Customize the readme.md file for your project.