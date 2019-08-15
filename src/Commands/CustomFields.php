<?php

namespace Torann\LaravelAsana\Commands;

use Illuminate\Console\Command;
use Asana;

class CustomFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asana:custom-fields {workspaceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the custom fields for this workspace.';

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

        $fields = Asana::getCustomFields($workspaceId);

        if (empty($fields->data)) {
            $this->info("\nno custom fields\n");
            return;
        }

        $fields = collect($fields->data)->map(function($row) {
            return [
                'id'      => $row->id,
                'name'    => $row->name,
                'type'    => $row->type,
                'options' => isset($row->enum_options) ? implode(', ', array_pluck($row->enum_options, 'name')) : '',
            ];
        });

        $this->table([
            'id',
            'name',
            'type',
            'options',
        ], $fields);
    }
}