<?php

namespace App\Http\Controllers\Frontend\Api;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\CoreConstants;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Contracts\MessageInterface;
use App\Services\Contracts\FrontendInterface;
use Illuminate\Validation\ValidationException;


class GeneralController extends Controller
{
    /**
     * @var FrontendInterface
     */
    private $frontend;

    /**
     * Create a new instance
     *
     * @param FrontendInterface $frontend
     * @return void
     */
    public function __construct(FrontendInterface $frontend)
    {
        $this->frontend = $frontend;
    }

    /**
     * Get all projects
     *
     * @return JsonResponse
     */
    public function getProjects()
    {
        $result = $this->frontend->getAllProjects();

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Store a new message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->filled('website')) {
            return response()->json([
                'message' => [
                    'website' => ['Spam detected.']
                ],
            ], 422);
        }
        try {
            // Validation for the incoming request parameters.
            $validator = Validator::make($request->all(), [
                 'name' => 'required|string|max:120',
            'email' => 'required|email',
            'subject' => 'required|string|max:150',
            'body' => 'required|string',
                'g-recaptcha-response' => 'google_recaptcha',
            ]);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
            $validatedData = $request->only('name', 'email', 'subject', 'body');

            $result = resolve(MessageInterface::class)->store($validatedData);

            return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
        } catch (ValidationException $exception) {
            // Handle validation errors.
            $errors = $exception->validator->errors()->toArray();
            return response()->json([
                "message" => $errors,
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                "message" => $exception->getMessage(),
            ], 500);
        }
    }
}
