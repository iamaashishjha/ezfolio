<?php

declare(strict_types=1);

namespace Src\Modules\About\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpsertAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array(
            'name' => 'required|string',
            'email' => 'required|email',
            'job_title' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'hero_subtitle' => 'nullable|string',
            'seederCV' => 'nullable|string',
            'taglines' => 'nullable|array',
            'taglines.*' => 'nullable|string',
            'about_highlights' => 'nullable|array',
            'about_highlights.*' => 'nullable|string',
            'social_links' => 'nullable|array',
            'social_links.*.title' => 'nullable|string',
            'social_links.*.link' => 'nullable|string',
            'social_links.*.iconClass' => 'nullable|string',
        );
    }
}
