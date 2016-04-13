# Asana for Laravel 5

[![Latest Stable Version](https://poser.pugx.org/torann/laravel-asana/v/stable.png)](https://packagist.org/packages/torann/laravel-asana) [![Total Downloads](https://poser.pugx.org/torann/laravel-asana/downloads.png)](https://packagist.org/packages/torann/laravel-asana)

----------

## Installation

- [Laravel Asana on Packagist](https://packagist.org/packages/torann/laravel-asana)
- [Laravel Asana on GitHub](https://github.com/torann/laravel-asana)
- [Laravel 4 Installation](https://github.com/Torann/laravel-asana/tree/0.1.2)

### Composer

From the command line run:

```
$ composer require torann/laravel-asana
```

### Laravel

Once installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

``` php
'providers' => [

    \Torann\LaravelAsana\ServiceProvider::class,

]
```

### Lumen

For Lumen register the service provider in `bootstrap/app.php`.

``` php
$app->register(\Torann\LaravelAsana\ServiceProvider::class);
```

### Facade

This package also ships with a facade which provides the static syntax for creating collections. You can register the facade in the aliases key of your `config/app.php` file.

```php
'aliases' => [
    'Asana' => 'Torann\LaravelAsana\Facade',
]
```

### Publish the configurations

Run this on the command line from the root of your project:

```
$ php artisan vendor:publish --provider="Torann\LaravelAsana\ServiceProvider"
```

A configuration file will be publish to `config/asana.php`.

## Quick Examples

#### Get a specific user

```php
Asana::getUserInfo($user_id);
```

#### Get current user

Will return the user's info of the owner of the Personal Access Token.

```php
Asana::getCurrentUser();
```

#### Get all users in all workspaces

Will return the user's info of the owner of the Personal Access Token.

```php
Asana::getUsers();
```

#### Get task

```php
Asana::getTask($task_id);
```

#### Get a task's sub-tasks

```php
Asana::getSubTasks($task_id);
```

#### Creating a task

```php
Asana::createTask([
   'workspace' => '176825', // Workspace ID
   'name'      => 'Hello World!', // Name of task
   'assignee'  => 'foo@bar.com', // Assign task to...
   'followers' => ['3714136', '5900783'] // We add some followers to the task... (this time by ID)
]);
```

#### Delete a task

```php
Asana::deleteTask($task_id);
```

#### Adding task to project

```php
Asana::addProjectToTask($task_id, $project_id);
```

#### Remove task from a project

```php
Asana::removeProjectToTask($task_id, $project_id);
```

#### Get task stories

```php
Asana::getTaskStories($task_id);
```

#### Commenting on a task

```php
Asana::commentOnTask($task_id, "Please please! Don't assign me this task!");
```

#### Add a tag to a task

```php
Asana::addTagToTask($task_id, $tag_id);
```

#### Remove a tag from a task

```php
Asana::removeTagFromTask($task_id, $tag_id);
```

#### Create a project

```php
Asana::createProject([
    "workspace" => "1768",
    "name"      => "Foo Project!",
    "notes"     => "This is a test project"
]);
```

#### Getting projects in all workspaces

```php
Asana::getProjects();
```

#### Get projects in a workspace

```php
$archived = false;

Asana::getProjectsInWorkspace($workspace_id, $archived);
```

#### Updating project info

```php
Asana::updateProject($project_id, [
    'name' => 'This is a new cool project!',
    'notes' => 'At first, it wasn't cool, but after this name change, it is!'
]);
```

#### Get project tasks

```php
Asana::getProjectTasks($project_id);
```

#### Get project stories

```php
Asana::getProjectStories($project_id);
```

#### Get a specific story

```php
Asana::getSingleStory($story_id);
```

#### Comment on a project

```php
$text = "Such fun!";

Asana::commentOnProject($project_id, $text)
```

#### Get a specific tag

```php
Asana::getTag($tag_id);
```

#### Get tags

```php
Asana::getTags();
```

#### Update tag

```php
// $data - array - An array containing fields to update, see Asana API if needed.

Asana::updateTag($tag_id, $data);
```

#### Get tasks with tag

```php
Asana::getTasksWithTag($tag_id);
```

#### Get workspaces

```php
Asana::getWorkspaces();
```

#### Update workspace

```php
$data = ['name' => ''];

Asana::updateWorkspace($workspace_i, $data);
```

#### Get workspace tasks

```php
// Assignee can either be 'me' or a user's ID

Asana::getWorkspaceTasks($workspace_id, $assignee);
```

#### Get workspace tags

```php
Asana::getWorkspaceTags($workspace_id);
```

#### Get workspace users

```php
Asana::getWorkspaceUsers($workspace_id);
```

#### Filtering

If you specify an assignee, you must also specify a workspace to filter on.

```php
Asana::getTasksByFilter([
    'assignee'  => 1121,
    'project'   => 37373729,
    'workspace' => 111221
]);
```

## Upgrading

### Upgrade from v0.2 to v0.3

Asana stopped supporting API keys, so now we must use a Personal Access Token. See Asana's directions for generating a [personal access tokens](https://asana.com/guide/help/api/api#gl-access-tokens). Then update the `config/asana.php` config file with the new token:

```php
'accessToken' => env('ASANA_TOKEN'),
```

## Change Log

#### v0.3.0

- Remove API key (deprecated) support

#### v0.2.1

- Add support for Lumen
- Code cleanup

#### v0.2.0

- Update to Laravel 5

#### v0.1.1

- Code cleanup