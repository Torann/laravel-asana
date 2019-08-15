<?php

namespace Torann\LaravelAsana\Commands;

use Illuminate\Console\Command;
use Asana;

class Users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asana:users {workspaceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the users in this workspace.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $workspaceId = $this->argument('workspaceId');

        if (!$workspaceId) {
            $workspaceId = config('asana.workspaceId');
        }

        $data = Asana::getUsers();

        if (empty($data->data)) {
            $this->info("\nno users\n");
            return;
        }

        $users = [];
        for ($i=0; $i < count($data->data); $i++) {
            $users[$i] = (array) $data->data[$i];
        }

        $this->table(array_keys($users[0]), $users);
    }
}