<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array(
            'MAIL_MAILER' => 'required',
            'MAIL_HOST' => 'required',
            'MAIL_PORT' => 'required',
            'MAIL_USERNAME' => 'required',
            'MAIL_PASSWORD' => 'required',
            'MAIL_ENCRYPTION' => 'required',
            'MAIL_FROM_ADDRESS' => 'nullable',
            'MAIL_FROM_NAME' => 'nullable',
        );
    }
}
