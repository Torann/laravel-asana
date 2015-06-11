<?php namespace Torann\LaravelAsana;

use Exception;
use InvalidArgumentException;

class Asana {

    /**
     * AsanaCurl instance
     *
     * @var \Torann\LaravelAsana\AsanaCurl
     */
    private $curl;

    /**
     * Default workspace
     *
     * @var int
     */
    public $defaultWorkspaceId;

    /**
     * Default project
     *
     * @var int
     */
    public $defaultProjectId;

    /**
     * Constructor
     *
     * @param  array $config
     * @throws Exception
     */
    public function __construct($config)
    {
        // Initiate curl
        $this->curl = new AsanaCurl(array_get($config, 'key'), array_get($config, 'accessToken'));

        // Set defaults
        $this->defaultWorkspaceId = $config['workspaceId'];
        $this->defaultProjectId = $config['projectId'];
    }

    /**
     * Returns the full user record for a single user.
     *
     * @param  string $userId
     * @return string JSON or null
     */
    public function getUserInfo($userId = null)
    {
        return $this->curl->get("users/{$userId}");
    }

    /**
     * Returns the full user record for the current user.
     *
     * @return string JSON or null
     */
    public function getCurrentUser()
    {
        return $this->curl->get('users/me');
    }

    /**
     * Returns the user records for all users in all workspaces you have access.
     *
     * @return string JSON or null
     */
    public function getUsers()
    {
        return $this->curl->get('users');
    }

    /**
     * Function to create a task.
     * For assign or remove the task to a project, use the addProjectToTask and removeProjectToTask.
     *
     *
     * @param array $data Array of data for the task following the Asana API documentation.
     * Example:
     *
     * array(
     *     "workspace" => "1768",
     *     "name" => "Hello World!",
     *     "notes" => "This is a task for testing the Asana API :)",
     *     "assignee" => "176822166183",
     *     "followers" => array(
     *         "37136",
     *         "59083"
     *     )
     * )
     *
     * @return string JSON or null
     */
    public function createTask($data)
    {
        $data = array_merge(array(
            'workspace' => $this->defaultWorkspaceId,
            'projects'  => $this->defaultProjectId
        ), $data);

        return $this->curl->post('tasks', array('data' => $data));
    }

    /**
     * Returns task information
     *
     * @param  string $taskId
     * @return string JSON or null
     */
    public function getTask($taskId)
    {
        return $this->curl->get("tasks/{$taskId}");
    }

    /**
     * Returns sub-task information
     *
     * @param  string $taskId
     * @return string JSON or null
     */
    public function getSubTasks($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/subtasks");
    }

    /**
     * Updates a task
     *
     * @param  string $taskId
     * @param  array $data See, createTask function comments for proper parameter info.
     * @return string JSON or null
     */
    public function updateTask($taskId, $data)
    {
        return $this->curl->put("tasks/{$taskId}", array('data' => $data));
    }

    /**
     * Delete a task
     *
     * @param  string $taskId
     * @return string JSON or null
     */
    public function deleteTask($taskId)
    {
        return $this->curl->delete("tasks/{$taskId}");
    }

    /**
     * Add Attachment to a task
     *
     * @param  string $taskId
     * @param  array $file
     * @return string JSON or null
     */
    public function addTaskAttachment($taskId, $file)
    {
        $data = array(
            'file' => $this->addPostFile($file)
        );

        return $this->curl->post("tasks/{$taskId}/attachments", $data);
    }

    /**
     * Returns the projects associated to the task.
     *
     * @param  string $taskId
     * @return string JSON or null
     */
    public function getProjectsForTask($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/projects");
    }

    /**
     * Adds a project to task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $projectId
     * @return string JSON or null
     */
    public function addProjectToTask($taskId, $projectId = null)
    {
        $data = array(
            'project' => $projectId ?: $this->defaultProjectId
        );

        return $this->curl->post("tasks/{$taskId}/addProject", array('data' => $data));
    }

    /**
     * Removes project from task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $projectId
     * @return string JSON or null
     */
    public function removeProjectToTask($taskId, $projectId = null)
    {
        $data = array(
            'project' => $projectId ?: $this->defaultProjectId
        );

        return $this->curl->post("tasks/{$taskId}/removeProject", array('data' => $data));
    }

    /**
     * Returns task by a given filter.
     * For now (limited by Asana API), you may limit your query either to a specific project or to an assignee and workspace
     *
     * NOTE: As Asana API says, if you filter by assignee, you MUST specify a workspaceId and vice-a-versa.
     *
     * @param  array $filter The filter with optional values.
     *
     * array(
     *     "assignee" => "",
     *     "project" => 0,
     *     "workspace" => 0
     * )
     *
     * @return string JSON or null
     */
    public function getTasksByFilter($filter = array("assignee" => "", "project" => "", "workspace" => ""))
    {
        $url = "";
        $filter = array_merge(array("assignee" => "", "project" => "", "workspace" => ""), $filter);
        $url .= $filter["assignee"] != ""?"&assignee={$filter["assignee"]}":"";
        $url .= $filter["project"] != ""?"&project={$filter["project"]}":"";
        $url .= $filter["workspace"] != ""?"&workspace={$filter["workspace"]}":"";
        if(strlen($url) > 0) $url = "?".substr($url, 1);

        return $this->curl->get("tasks{$url}");
    }

    /**
     * Returns the list of stories associated with the object.
     * As usual with queries, stories are returned in compact form.
     * However, the compact form for stories contains more information by default than just the ID.
     * There is presently no way to get a filtered set of stories.
     *
     * @param  string $taskId
     * @return string JSON or null
     */
    public function getTaskStories($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/stories");
    }

    /**
     * Adds a comment to a task.
     * The comment will be authored by the authorized user, and timestamped when the server receives the request.
     *
     * @param  string $taskId
     * @param  string $text
     * @return string JSON or null
     */
    public function commentOnTask($taskId, $text = "")
    {
        $data = array(
            'text' => $text
        );

        return $this->curl->post("tasks/{$taskId}/stories", array('data' => $data));
    }

    /**
     * Adds a tag to a task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $tagId
     * @return string JSON or null
     */
    public function addTagToTask($taskId, $tagId)
    {
        $data = array(
            "tag" => $tagId
        );

        return $this->curl->post("tasks/{$taskId}/addTag", array('data' => $data));
    }

    /**
     * Removes a tag from a task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $tagId
     * @return string JSON or null
     */
    public function removeTagFromTask($taskId, $tagId)
    {
        $data = array(
            "tag" => $tagId
        );

        return $this->curl->post("tasks/{$taskId}/removeTag", array('data' => $data));
    }

    /**
     * Function to create a project.
     *
     * @param array $data Array of data for the project following the Asana API documentation.
     * Example:
     *
     * array(
     *     "workspace" => "1768",
     *     "name" => "Foo Project!",
     *     "notes" => "This is a test project"
     * )
     *
     * @return string JSON or null
     */
    public function createProject($data)
    {
        return $this->curl->post('projects', array('data' => $data));
    }

    /**
     * Returns the full record for a single project.
     *
     * @param  string $projectId
     * @return string JSON or null
     */
    public function getProject($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("projects/{$projectId}");
    }

    /**
     * Returns the projects in all workspaces containing archived ones or not.
     *
     * @param  boolean $archived Return archived projects or not
     * @param  string  $opt_fields Return results with optional parameters
     * @return string  JSON or null
     */
    public function getProjects($archived = false, $opt_fields = "")
    {
        $archived = $archived?"true":"false";
        $opt_fields = ($opt_fields != "")?"&opt_fields={$opt_fields}":"";

        return $this->curl->get("projects?archived={$archived}{$opt_fields}");
    }

    /**
     * Returns the projects in provided workspace containing archived ones or not.
     *
     * @param  string  $workspaceId
     * @param  boolean $archived Return archived projects or not
     * @return string  JSON or null
     */
    public function getProjectsInWorkspace($workspaceId = null, $archived = false)
    {
        $archived = $archived ? 1 : 0;
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->get("projects?archived={$archived}&workspace={$workspaceId}");
    }

    /**
     * This method modifies the fields of a project provided in the request, then returns the full updated record.
     *
     * @param  string $projectId
     * @param  array  $data An array containing fields to update, see Asana API if needed.
     * @return string JSON or null
     */
    public function updateProject($projectId = null, $data)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->put("projects/{$projectId}", array('data' => $data));
    }

    /**
     * Returns all unarchived tasks of a given project
     *
     * @param  string $projectId
     * @return string JSON or null
     */
    public function getProjectTasks($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("tasks?project={$projectId}");
    }

    /**
     * Returns the list of stories associated with the object.
     * As usual with queries, stories are returned in compact form.
     * However, the compact form for stories contains more
     * information by default than just the ID.
     * There is presently no way to get a filtered set of stories.
     *
     * @param  string $projectId
     * @return string JSON or null
     */
    public function getProjectStories($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("projects/{$projectId}/stories");
    }

    /**
     * Adds a comment to a project
     * The comment will be authored by the authorized user, and timestamped when the server receives the request.
     *
     * @param  string $projectId
     * @param  string $text
     * @return string JSON or null
     */
    public function commentOnProject($projectId = null, $text = "")
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        $data = array(
           "text" => $text
        );

        return $this->curl->post("projects/{$projectId}/stories", array('data' => $data));
    }

    /**
     * Returns the full record for a single tag.
     *
     * @param  string $tagId
     * @return string JSON or null
     */
    public function getTag($tagId)
    {
        return $this->curl->get("tags/{$tagId}");
    }

    /**
     * Returns the full record for all tags in all workspaces.
     *
     * @return string JSON or null
     */
    public function getTags()
    {
        return $this->curl->get('tags');
    }

    /**
     * Modifies the fields of a tag provided in the request, then returns the full updated record.
     *
     * @param  string $tagId
     * @param  array $data An array containing fields to update, see Asana API if needed.
     * @return string JSON or null
     */
    public function updateTag($tagId, $data)
    {
        return $this->curl->put("tags/{$tagId}", array('data' => $data));
    }

    /**
     * Returns the list of all tasks with this tag. Tasks can have more than one tag at a time.
     *
     * @param  string $tagId
     * @return string JSON or null
     */
    public function getTasksWithTag($tagId)
    {
        return $this->curl->get("tags/{$tagId}/tasks");
    }

    /**
     * Returns the full record for a single story.
     *
     * @param  string $storyId
     * @return string JSON or null
     */
    public function getSingleStory($storyId)
    {
        return $this->curl->get("stories/{$storyId}");
    }

    /**
     * Returns all the workspaces.
     *
     * @return string JSON or null
     */
    public function getWorkspaces()
    {
        return $this->curl->get('workspaces');
    }

    /**
     * Currently the only field that can be modified for a workspace is its name (as Asana API says).
     * This method returns the complete updated workspace record.
     *
     * @param  array  $data
     * @return string JSON or null
     */
    public function updateWorkspace($workspaceId = null, $data = array("name" => ""))
    {
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->put("workspaces/{$workspaceId}", array('data' => $data));
    }

    /**
     * Returns tasks of all workspace assigned to someone.
     * Note: As Asana API says, you must specify an assignee when querying for workspace tasks.
     *
     * @param  string $workspaceId The id of the workspace
     * @param  string $assignee Can be "me" or user ID
     *
     * @return string JSON or null
     */
    public function getWorkspaceTasks($workspaceId = null, $assignee = "me")
    {
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->get("tasks?workspace={$workspaceId}&assignee={$assignee}");
    }

    /**
     * Returns tags of all workspace.
     *
     * @param string $workspaceId The id of the workspace
     * @return string JSON or null
     */
    public function getWorkspaceTags($workspaceId = null)
    {
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->get("workspaces/{$workspaceId}/tags");
    }

    /**
     * Returns users of all workspace.
     *
     * @param  string $workspaceId The id of the workspace
     * @return string JSON or null
     */
    public function getWorkspaceUsers($workspaceId = null)
    {
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->get("workspaces/{$workspaceId}/users");
    }

    /**
     * Returns events of a given project
     *
     * @param  string $projectId The id of the project
     * @return string JSON or null
     */
    public function getProjectEvents($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;
        
        return $this->curl->get("projects/{$projectId}/events");
    }

    /**
     * Return event sync key
     *
     * @return mixed
     */
    public function getSyncKey()
    {
        return $this->curl->getSyncKey();
    }

    /**
     * Return error
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->curl->getErrors();
    }
}
