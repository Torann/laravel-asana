<?php

namespace Torann\LaravelAsana;

use Exception;

class Asana
{
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
     *
     * @throws Exception
     */
    public function __construct($config)
    {
        // Initiate curl
        $this->curl = new AsanaCurl($config['accessToken']);

        // Set defaults
        $this->defaultWorkspaceId = $config['workspaceId'];
        $this->defaultProjectId = $config['projectId'];
    }

    /**
     * Returns the full user record for a single user.
     *
     * @param  string $userId
     *
     * @return string|null
     */
    public function getUserInfo($userId = null)
    {
        return $this->curl->get("users/{$userId}");
    }

    /**
     * Returns the full user record for the current user.
     *
     * @return string|null
     */
    public function getCurrentUser()
    {
        return $this->curl->get('users/me');
    }

    /**
     * Returns the user records for all users in all workspaces you have access.
     *
     * @return string|null
     */
    public function getUsers($opt_fields = null)
    {
        $url = $opt_fields ? 'users?opt_fields=' . $opt_fields : 'users';
        return $this->curl->get($url);
    }

    /**
     * Function to create a task.
     *
     * For assign or remove the task to a project, use the addProjectToTask and removeProjectToTask.
     *
     * @param array $data
     *
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
     * @return object|null
     */
    public function createTask($data)
    {
        $data = array_merge([
            'workspace' => $this->defaultWorkspaceId,
            'projects' => $this->defaultProjectId
        ], $data);

        return $this->curl->post('tasks', ['data' => $data]);
    }

    /**
     * Returns task information
     *
     * @param  string $taskId
     *
     * @return string|null
     */
    public function getTask($taskId)
    {
        return $this->curl->get("tasks/{$taskId}");
    }

    /**
     * Returns sub-task information
     *
     * @param  string $taskId
     *
     * @return string|null
     */
    public function getSubTasks($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/subtasks");
    }

    /**
     * Updates a task
     *
     * @param  string $taskId
     * @param  array  $data
     *
     * @return string|null
     */
    public function updateTask($taskId, $data)
    {
        return $this->curl->put("tasks/{$taskId}", ['data' => $data]);
    }

    /**
     * Delete a task
     *
     * @param  string $taskId
     *
     * @return string|null
     */
    public function deleteTask($taskId)
    {
        return $this->curl->delete("tasks/{$taskId}");
    }

    /**
     * Add Attachment to a task
     *
     * @param  string $taskId
     * @param  array  $file
     *
     * @return string|null
     */
    public function addTaskAttachment($taskId, $file)
    {
        return $this->curl->post("tasks/{$taskId}/attachments", [
            'file' => $file,
        ]);
    }

	/**
	 * getAllAttachments
	 *
	 * Gets a List of all available Attachments.
	 *
	 * @param $taskId
	 *
	 * @return null|string
	 * @author  Olly Warren, Big Bite Creative
	 * @package Torann\LaravelAsana
	 * @version 1.0
	 */
	public function getAllAttachments($taskId)
	{
		return $this->curl->get("tasks/{$taskId}/attachments");
	}

	/**
	 * getSingleAttachment
	 *
	 * Gets a Single Attachment based on a file id.
	 *
	 * @param $attachmentId
	 *
	 * @return null|string
	 * @author  Olly Warren, Big Bite Creative
	 * @package Torann\LaravelAsana
	 * @version 1.0
	 */
	public function getSingleAttachment($attachmentId)
	{
		return $this->curl->get("attachments/{$attachmentId}");
	}

    /**
     * Returns the projects associated to the task.
     *
     * @param  string $taskId
     *
     * @return string|null
     */
    public function getProjectsForTask($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/projects");
    }

    /**
     * Adds a project to task. If successful, will
     * return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $projectId
     *
     * @return string|null
     */
    public function addProjectToTask($taskId, $projectId = null)
    {
        $data = [
            'project' => $projectId ?: $this->defaultProjectId
        ];

        return $this->curl->post("tasks/{$taskId}/addProject", ['data' => $data]);
    }

    /**
     * Removes project from task. If successful, will
     * return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $projectId
     *
     * @return string|null
     */
    public function removeProjectToTask($taskId, $projectId = null)
    {
        $data = [
            'project' => $projectId ?: $this->defaultProjectId
        ];

        return $this->curl->post("tasks/{$taskId}/removeProject", ['data' => $data]);
    }

    /**
     * Returns task by a given filter.
     *
     * For now (limited by Asana API), you may limit your
     * query either to a specific project or to an assignee and workspace
     *
     * NOTE: As Asana API says, if you filter by assignee, you MUST specify a workspaceId and vice-a-versa.
     *
     * @param array $filter
     *
     * array(
     *     "assignee" => "",
     *     "project" => 0,
     *     "workspace" => 0
     * )
     *
     * @return string|null
     */
    public function getTasksByFilter($filter = ["assignee" => "", "project" => "", "workspace" => ""])
    {
        $filter = array_filter(array_merge(["assignee" => "", "project" => "", "workspace" => ""], $filter));
        $url = '?' . join('&', array_map(function ($k, $v) {
                return "{$k}={$v}";
            }, array_keys($filter), $filter));

        return $this->curl->get("tasks{$url}");
    }

    /**
     * Returns the list of stories associated with the object.
     * As usual with queries, stories are returned in compact form.
     * However, the compact form for stories contains more information by default than just the ID.
     *
     * There is presently no way to get a filtered set of stories.
     *
     * @param  string $taskId
     *
     * @return string|null
     */
    public function getTaskStories($taskId)
    {
        return $this->curl->get("tasks/{$taskId}/stories");
    }

    /**
     * Adds a comment to a task.
     *
     * The comment will be authored by the authorized user, and
     * timestamped when the server receives the request.
     *
     * @param  string $taskId
     * @param  string $text
     *
     * @return string|null
     */
    public function commentOnTask($taskId, $text = "")
    {
        $data = [
            'text' => $text
        ];

        return $this->curl->post("tasks/{$taskId}/stories", ['data' => $data]);
    }

    /**
     * Adds a tag to a task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $tagId
     *
     * @return string|null
     */
    public function addTagToTask($taskId, $tagId)
    {
        $data = [
            "tag" => $tagId
        ];

        return $this->curl->post("tasks/{$taskId}/addTag", ['data' => $data]);
    }

    /**
     * Removes a tag from a task. If successful, will return success and an empty data block.
     *
     * @param  string $taskId
     * @param  string $tagId
     *
     * @return string|null
     */
    public function removeTagFromTask($taskId, $tagId)
    {
        $data = [
            "tag" => $tagId
        ];

        return $this->curl->post("tasks/{$taskId}/removeTag", ['data' => $data]);
    }

    /**
     * Function to create a project.
     *
     * @param array $data Array of data for the project following the Asana API documentation.
     *
     * Example:
     *
     * array(
     *     "workspace" => "1768",
     *     "name" => "Foo Project!",
     *     "notes" => "This is a test project"
     * )
     *
     * @return string|null
     */
    public function createProject($data)
    {
        return $this->curl->post('projects', ['data' => $data]);
    }

    /**
     * Returns the full record for a single project.
     *
     * @param  string $projectId
     *
     * @return string|null
     */
    public function getProject($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("projects/{$projectId}");
    }

    /**
     * Returns the projects in all workspaces containing archived ones or not.
     *
     * @param  boolean $archived   Return archived projects or not
     * @param  string  $opt_fields Return results with optional parameters
     *
     * @return string  JSON or null
     */
    public function getProjects($archived = false, $opt_fields = "")
    {
        $archived = $archived ? "true" : "false";
        $opt_fields = ($opt_fields != "") ? "&opt_fields={$opt_fields}" : "";

        return $this->curl->get("projects?archived={$archived}{$opt_fields}");
    }

    /**
     * Returns the projects in provided workspace containing archived ones or not.
     *
     * @param  string  $workspaceId
     * @param  boolean $archived Return archived projects or not
     *
     * @return string  JSON or null
     */
    public function getProjectsInWorkspace($workspaceId = null, $archived = false)
    {
        $archived = $archived ? 1 : 0;
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->get("projects?archived={$archived}&workspace={$workspaceId}");
    }

    /**
     * This method modifies the fields of a project provided
     * in the request, then returns the full updated record.
     *
     * @param  string $projectId
     * @param  array  $data
     *
     * @return string|null
     */
    public function updateProject($projectId = null, $data)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->put("projects/{$projectId}", ['data' => $data]);
    }

    /**
     * Returns all unarchived tasks of a given project
     *
     * @param  string $projectId
     *
     * @return string|null
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
     *
     * @return string|null
     */
    public function getProjectStories($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("projects/{$projectId}/stories");
    }

    /**
     * Adds a comment to a project
     *
     * The comment will be authored by the authorized user, and
     * timestamped when the server receives the request.
     *
     * @param  string $projectId
     * @param  string $text
     *
     * @return string|null
     */
    public function commentOnProject($projectId = null, $text = "")
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        $data = [
            "text" => $text
        ];

        return $this->curl->post("projects/{$projectId}/stories", ['data' => $data]);
    }

    /**
     * Returns the full record for a single tag.
     *
     * @param  string $tagId
     *
     * @return string|null
     */
    public function getTag($tagId)
    {
        return $this->curl->get("tags/{$tagId}");
    }

    /**
     * Returns the full record for all tags in all workspaces.
     *
     * @return string|null
     */
    public function getTags()
    {
        return $this->curl->get('tags');
    }

    /**
     * Modifies the fields of a tag provided in the request,
     * then returns the full updated record.
     *
     * @param  string $tagId
     * @param  array  $data
     *
     * @return string|null
     */
    public function updateTag($tagId, $data)
    {
        return $this->curl->put("tags/{$tagId}", ['data' => $data]);
    }

    /**
     * Returns the list of all tasks with this tag. Tasks
     * can have more than one tag at a time.
     *
     * @param  string $tagId
     *
     * @return string|null
     */
    public function getTasksWithTag($tagId)
    {
        return $this->curl->get("tags/{$tagId}/tasks");
    }

    /**
     * Returns the full record for a single story.
     *
     * @param  string $storyId
     *
     * @return string|null
     */
    public function getSingleStory($storyId)
    {
        return $this->curl->get("stories/{$storyId}");
    }

    /**
     * Returns all the workspaces.
     *
     * @return string|null
     */
    public function getWorkspaces()
    {
        return $this->curl->get('workspaces');
    }

    /**
     * Currently the only field that can be modified for a
     * workspace is its name (as Asana API says).
     *
     * This method returns the complete updated workspace record.
     *
     * @param  array $data
     *
     * @return string|null
     */
    public function updateWorkspace($workspaceId = null, $data = ["name" => ""])
    {
        $workspaceId = $workspaceId ?: $this->defaultWorkspaceId;

        return $this->curl->put("workspaces/{$workspaceId}", ['data' => $data]);
    }

    /**
     * Returns tasks of all workspace assigned to someone.
     *
     * Note: As Asana API says, you must specify an assignee when querying for workspace tasks.
     *
     * @param  string $workspaceId The id of the workspace
     * @param  string $assignee    Can be "me" or user ID
     *
     * @return string|null
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
     *
     * @return string|null
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
     *
     * @return string|null
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
     *
     * @return string|null
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

	/**
	 * getCustomFields
	 *
	 * Returns tall custom fields for a workspace
	 *
	 * @param $workspaceId
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function getCustomFields($workspaceId)
	{
		return $this->curl->get("workspaces/{$workspaceId}/custom_fields");
	}

	/**
	 * getCustomField
	 *
	 * Returns the full details on the custom field passed in.
	 *
	 * @param $fieldId
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function getCustomField($fieldId)
	{
		return $this->curl->get("custom_fields/{$fieldId}");
	}

	/**
	 * createWebhook
	 *
	 * Creates a webhook with asana based
	 * Requires the resource to link with (Workspace, Project)
	 * and a Target URL for your API/Application.
	 *
	 * Note: Will send a handshake to your Application with a
	 * X-Security-Header that must be returned with a 200
	 * Response to verify the webhook creation. Asana may then
	 * follow up with a "heartbeat" request that will contain an
	 * empty "events" JSON object and a X-Signature-Header.
	 *
	 * @param $resourceId
	 * @param $targetUrl
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function createWebhook($resourceId, $targetUrl)
	{
		//Define the Data array to include in the request
		$data = [
			'data' => [
				'resource'  => $resourceId,
				'target'    => $targetUrl
			]
		];

		return $this->curl->post("webhooks", $data);
	}

	/**
	 * getWebhook
	 *
	 * Gets the full details for a Webhook.
	 *
	 * @param $webhookId
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function getWebhook($webhookId)
	{
		return $this->curl->get("webhooks/{$webhookId}");
	}

	/**
	 * getWebhooks
	 *
	 * Gets all the webhooks for a workspace
	 *
	 * @param $workspaceId
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function getWebhooks($workspaceId)
	{
		return $this->curl->get("webhooks?workspace={$workspaceId}");
	}



	/**
	 * deleteWebhook
	 *
	 * Removes a webhook from Asana.
	 *
	 * @param $webhookId
	 *
	 * @return null|string
	 * @author Olly Warren https://github.com/ollywarren
	 * @version 1.0
	 */
	public function deleteWebhook($webhookId)
	{
		return $this->curl->delete("webhooks/{$webhookId}");
	}

    /**
     * Creates a new section in a project.
     *
     * Returns the full record of the newly created section.
     *
     * @param  project The project to create the section in
     * @return response
     */
    public function createSection($data, $projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        $data = array_merge([
            'projects' => $projectId
        ], $data);

        return $this->curl->post("projects/{$projectId}/sections", ['data' => $data]);
    }

    /**
     * Returns the compact records for all sections in the specified project.
     *
     * @param  project The project to get sections from.
     * @return response
     */
    public function getProjectSections($projectId = null)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->get("projects/{$projectId}/sections");
    }

    /**
     * Returns the complete record for a single section.
     *
     * @param  section The section to get.
     * @return response
     */
    public function getSection($sectionId)
    {
        return $this->curl->get("sections/{$sectionId}");
    }

    /**
     * A specific, existing section can be updated by making a PUT request on
     * the URL for that project. Only the fields provided in the `data` block
     * will be updated; any unspecified fields will remain unchanged. (note that
     * at this time, the only field that can be updated is the `name` field.)
     *
     * When using this method, it is best to specify only those fields you wish
     * to change, or else you may overwrite changes made by another user since
     * you last retrieved the task.
     *
     * Returns the complete updated section record.
     *
     * @param  section The section to update.
     * @return response
     */
    public function updateSection($sectionId, $data)
    {
        return $this->curl->put("sections/{$sectionId}", ['data' => $data]);
    }

    /**
     * A specific, existing section can be deleted by making a DELETE request
     * on the URL for that section.
     *
     * Note that sections must be empty to be deleted.
     *
     * The last remaining section in a board view cannot be deleted.
     *
     * Returns an empty data block.
     *
     * @param  section The section to delete.
     * @return response
     */
    public function deleteSection($sectionId)
    {
        return $this->curl->delete("sections/{$sectionId}");
    }

    /**
     * Move sections relative to each other in a board view. One of
     * `before_section` or `after_section` is required.
     *
     * Sections cannot be moved between projects.
     *
     * At this point in time, moving sections is not supported in list views, only board views.
     *
     * Returns an empty data block.
     *
     * @param  project The project in which to reorder the given section
     * @return response
     */
    public function moveSection($projectId, $data)
    {
        $projectId = $projectId ?: $this->defaultProjectId;

        return $this->curl->post("projects/{$projectId}/sections/insert", ['data' => $data]);
    }
}
