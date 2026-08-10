<?php

namespace App\Concerns;

use App\Repository\Eloquent\Project;

/**
 * Data Transfer Object for a project.
 *
 * Carries validated input into the domain layer and shapes the JSON output
 * returned by the API (replacing an Eloquent API Resource).
 */
class ProjectData
{
    public function __construct(
        public string $client_name,
        public string $project_name,
        public ?string $description,
        public string $status,
        public string $priority,
        public ?string $start_date,
        public ?string $due_date,
        public ?int $id = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    /**
     * Build a DTO from validated request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            client_name: $data['client_name'],
            project_name: $data['project_name'],
            description: $data['description'] ?? null,
            status: $data['status'],
            priority: $data['priority'],
            start_date: $data['start_date'] ?? null,
            due_date: $data['due_date'] ?? null,
        );
    }

    /**
     * Build a DTO from a persisted Project model.
     */
    public static function fromModel(Project $project): self
    {
        return new self(
            client_name: $project->client_name,
            project_name: $project->project_name,
            description: $project->description,
            status: $project->status->value,
            priority: $project->priority->value,
            start_date: $project->start_date?->toDateString(),
            due_date: $project->due_date?->toDateString(),
            id: $project->id,
            created_at: $project->created_at?->toIso8601String(),
            updated_at: $project->updated_at?->toIso8601String(),
        );
    }

    /**
     * The attributes that should be persisted to the database.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'client_name' => $this->client_name,
            'project_name' => $this->project_name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
        ];
    }

    /**
     * The JSON representation returned by the API.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            ...$this->toAttributes(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
