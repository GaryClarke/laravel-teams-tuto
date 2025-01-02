<?php

namespace App\Http\Requests;

use App\Models\Permission;
use App\Models\TeamInvite;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamInviteStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::INVITE_TO_TEAM, $this->route('team'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'max:255',
                Rule::unique(TeamInvite::class, 'email')
                    ->where('team_id', $this->route('team')->id),
            ],
        ];
    }
}
