<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
    $orgId = config('organizations.enabled') ? auth()->user()->organization_id : null;
        $projectId = $this->project->id ?? null;
        return [
            'name' => ['required','string','max:191'],
            'code' => [
                'required','string','max:64','alpha_dash',
                Rule::unique('projects','code')
                    ->where(fn($q) => $q->where('organization_id', $orgId))
                    ->ignore($projectId, 'id')
            ],
            'description' => ['nullable','string'],
            'is_active' => ['sometimes','boolean'],
            'billable' => ['sometimes','boolean'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
            'responsible_user_id' => [
                'required','integer',
                Rule::exists('users','id')->where(fn($q) => $q->where('organization_id', $orgId))
            ],
        ];
    }
}
