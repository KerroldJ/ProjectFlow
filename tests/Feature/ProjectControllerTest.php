<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Acme Corporation',
            'project_name' => 'Corporate Website Redesign',
            'description' => 'Redesign and modernize the corporate website.',
            'status' => 'In Progress',
            'priority' => 'High',
            'start_date' => '2026-06-01',
            'due_date' => '2026-07-15',
        ], $overrides);
    }

    public function test_the_landing_page_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_the_landing_page_redirects_authenticated_users_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_guests_cannot_access_projects(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_it_lists_projects(): void
    {
        $this->actingAs(User::factory()->create());
        Project::factory()->count(3)->create();

        $this->get(route('projects.index'))->assertOk();
    }

    public function test_it_filters_projects_by_status(): void
    {
        $this->actingAs(User::factory()->create());
        Project::factory()->create(['status' => 'Completed', 'client_name' => 'Done Co']);
        Project::factory()->create(['status' => 'Planning', 'client_name' => 'Todo Co']);

        $this->get(route('projects.index', ['status' => 'Completed']))
            ->assertInertia(fn ($page) => $page
                ->component('projects/Index')
                ->has('projects', 1)
                ->where('projects.0.client_name', 'Done Co')
            );
    }

    public function test_it_creates_a_project(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('projects.store'), $this->validPayload())
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'client_name' => 'Acme Corporation',
            'project_name' => 'Corporate Website Redesign',
            'status' => 'In Progress',
        ]);
    }

    public function test_it_requires_a_client_name_and_project_name(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('projects.store'), $this->validPayload([
            'client_name' => '',
            'project_name' => '',
        ]))->assertInvalid(['client_name', 'project_name']);
    }

    public function test_it_rejects_an_invalid_status_or_priority(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('projects.store'), $this->validPayload([
            'status' => 'Cancelled',
            'priority' => 'Urgent',
        ]))->assertInvalid(['status', 'priority']);
    }

    public function test_it_rejects_a_due_date_earlier_than_the_start_date(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('projects.store'), $this->validPayload([
            'start_date' => '2026-07-15',
            'due_date' => '2026-06-01',
        ]))->assertInvalid(['due_date']);
    }

    public function test_it_updates_a_project(): void
    {
        $this->actingAs(User::factory()->create());
        $project = Project::factory()->create();

        $this->put(route('projects.update', $project), $this->validPayload([
            'client_name' => 'Updated Client',
        ]))->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'client_name' => 'Updated Client',
        ]);
    }

    public function test_it_deletes_a_project(): void
    {
        $this->actingAs(User::factory()->create());
        $project = Project::factory()->create();

        $this->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
