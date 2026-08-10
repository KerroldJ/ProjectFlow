<?php

namespace App\Http\Requests;

use App\Domain\Project\Enums\ProjectPriority;
use App\Domain\Project\Enums\ProjectStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', new Enum(ProjectStatus::class)],
            'priority' => ['required', new Enum(ProjectPriority::class)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', Rule::when(
                $this->filled('start_date'),
                ['after_or_equal:start_date'],
            )],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'due_date.after_or_equal' => 'The due date cannot be earlier than the start date.',
        ];
    }

    /**
     * The valid status options (used to populate the UI).
     *
     * @return array<int, string>
     */
    public static function statusOptions(): array
    {
        return ProjectStatus::values();
    }

    /**
     * The valid priority options (used to populate the UI).
     *
     * @return array<int, string>
     */
    public static function priorityOptions(): array
    {
        return ProjectPriority::values();
    }
}
