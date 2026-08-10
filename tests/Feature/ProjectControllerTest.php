<?php

namespace Tests\Feature;

use App\Repository\Models\Project;
use App\Repository\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
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

    public function test_the_projects_page_redirects_guests_to_login(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_the_projects_page_renders_for_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('projects/Index'));
    }

    public function test_guests_cannot_access_the_api(): void
    {
        $this->getJson(route('api.projects.index'))->assertUnauthorized();
    }

    public function test_the_api_lists_projects(): void
    {
        $this->actingAsUser();
        Project::factory()->count(3)->create();

        $this->getJson(route('api.projects.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_the_api_paginates_projects(): void
    {
        $this->actingAsUser();
        Project::factory()->count(15)->create();

        $this->getJson(route('api.projects.index', ['per_page' => 10, 'page' => 1]))
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.current_page', 1);

        $this->getJson(route('api.projects.index', ['per_page' => 10, 'page' => 2]))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_the_api_filters_projects_by_status(): void
    {
        $this->actingAsUser();
        Project::factory()->create(['status' => 'Completed', 'client_name' => 'Done Co']);
        Project::factory()->create(['status' => 'Planning', 'client_name' => 'Todo Co']);

        $this->getJson(route('api.projects.index', ['status' => 'Completed']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.client_name', 'Done Co');
    }

    public function test_the_api_shows_a_single_project(): void
    {
        $this->actingAsUser();
        $project = Project::factory()->create();

        $this->getJson(route('api.projects.show', $project))
            ->assertOk()
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_the_api_creates_a_project(): void
    {
        $this->actingAsUser();

        $this->postJson(route('api.projects.store'), $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.client_name', 'Acme Corporation');

        $this->assertDatabaseHas('projects', [
            'project_name' => 'Corporate Website Redesign',
            'status' => 'In Progress',
        ]);
    }

    public function test_the_api_requires_a_client_name_and_project_name(): void
    {
        $this->actingAsUser();

        $this->postJson(route('api.projects.store'), $this->validPayload([
            'client_name' => '',
            'project_name' => '',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['client_name', 'project_name']);
    }

    public function test_the_api_rejects_an_invalid_status_or_priority(): void
    {
        $this->actingAsUser();

        $this->postJson(route('api.projects.store'), $this->validPayload([
            'status' => 'Cancelled',
            'priority' => 'Urgent',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['status', 'priority']);
    }

    public function test_the_api_rejects_a_due_date_earlier_than_the_start_date(): void
    {
        $this->actingAsUser();

        $this->postJson(route('api.projects.store'), $this->validPayload([
            'start_date' => '2026-07-15',
            'due_date' => '2026-06-01',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['due_date']);
    }

    public function test_the_api_updates_a_project(): void
    {
        $this->actingAsUser();
        $project = Project::factory()->create();

        $this->putJson(route('api.projects.update', $project), $this->validPayload([
            'client_name' => 'Updated Client',
        ]))->assertOk()->assertJsonPath('data.client_name', 'Updated Client');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'client_name' => 'Updated Client',
        ]);
    }

    public function test_the_api_deletes_a_project(): void
    {
        $this->actingAsUser();
        $project = Project::factory()->create();

        $this->deleteJson(route('api.projects.destroy', $project))->assertNoContent();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
