# Asana for Laravel

[![Latest Stable Version](https://poser.pugx.org/torann/laravel-asana/v/stable.png)](https://packagist.org/packages/torann/laravel-asana) [![Total Downloads](https://poser.pugx.org/torann/laravel-asana/downloads.png)](https://packagist.org/packages/torann/laravel-asana)

----------

## Installation

- [Laravel Asana on Packagist](https://packagist.org/packages/torann/laravel-asana)
- [Laravel Asana on GitHub](https://github.com/torann/laravel-asana)

To get the latest version of Laravel Asana simply require it in your `composer.json` file.

~~~
"torann/laravel-asana": "0.1.*@dev"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

### Create configuration file using artisan

```
$ php artisan config:publish torann/laravel-asana
```

## Quick Examples

#### Creating a task

```php
Asana::createTask(array(
   'workspace' => '176825', // Workspace ID
   'name'      => 'Hello World!', // Name of task
   'assignee'  => 'foo@bar.com', // Assign task to...
   'followers' => array('3714136', '5900783') // We add some followers to the task... (this time by ID)
));
```

#### Adding task to project

```php
Asana::addProjectToTask(:task_id, :project_id);
```

#### Commenting on a task

```php
Asana::commentOnTask(:task_id, 'Please please! Don't assign me this task!');
```

#### Getting projects in all workspaces

```php
Asana::getProjects();
```

#### Updating project info

```php
Asana::updateProject(:project_id, array(
    'name' => 'This is a new cool project!',
    'notes' => 'At first, it wasn't cool, but after this name change, it is!'
));
```

## Full Documentation

[View the official documentation](https://github.com/Torann/laravel-asana/wiki).