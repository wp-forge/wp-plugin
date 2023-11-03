# WordPress Plugin

A WordPress plugin scaffolding template that can be easily installed using Composer.

## Requirements

- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Node](https://nodejs.org/)
- [Docker](https://www.docker.com/) (optional)

## Installation

Run this command in the terminal:

```
composer --remove-vcs create-project wp-forge/wp-plugin
``` 

**Be sure to append a directory name to the end of the command to customize the folder name that your project will be
installed into!**

The installation process will check your environment for the required PHP version and PHP extensions. If this presents a
problem and you want to force install anyway, just add the `--ignore-platform-reqs` flag to the command.

## Notes

- The `.scripts` and `.templates` folders are used for scaffolding purposes only and won't exist in your end project.
- We use the `@wordpress/env` package to provide a local development environment. This package requires Docker to be
  installed on your machine. If you don't want to use Docker, you can remove the `@wordpress/env` package from the
  `package.json` file and delete the `.wp-env.json` file.
- We use the concept of a hooks folder. Rather than having a bunch of WordPress hooks scattered around your
  plugin, the `hooks` folder contains an `actions` and `filters` folder where each file name corresponds to the name of
  a hook (except with dashes in place of underscores). All items that belong in a particular hook are added to the
  appropriate file, making it easy to find, edit, and add hooks in a consistent way.
- This repo has a `source` folder, where you can store any files that require a build step (e.g. scss and js). The
  `assets` folder is where you store the compiled files that will be used in your plugin. By default, we .gitignore
  the `assets/css` and `assets/js` folders.
- Currently, this template does not implement any kind of build process. This is something that will be added in the
  future.
- This repo creates an `.nvmrc` file. If you use [NVM](https://github.com/nvm-sh/nvm), you'll be able to run `nvm use`
  to automatically switch to the correct version of Node for this project. It will default to the version of Node that
  is running on your machine at the time of install.
- This template creates GitHub Action workflows for your plugin. If you are publicly releasing your plugin on the
  WordPress.org plugin directory, you will need to add a `SVN_USERNAME` and `SVN_PASSWORD` environment variable to your
  GitHub repository settings.
- If your plugin is to be publicly released, the `.wporg` folder is used to store the assets that will be uploaded to
  the WordPress.org plugin repository.

## Reminders

Don't forget to:

- Run `git remote add origin <url>` to add your remote repository
- Add a `SVN_USERNAME` and `SVN_PASSWORD` environment variable to your GitHub repository settings (if your plugin is to
  be publicly released)