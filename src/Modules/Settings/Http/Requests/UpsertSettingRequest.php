<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpsertSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array(
            'setting_key' => 'required|integer',
            'setting_value' => 'required',
        );
    }
}
