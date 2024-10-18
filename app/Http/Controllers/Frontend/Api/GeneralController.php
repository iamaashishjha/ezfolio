<?php

namespace App\Http\Controllers\Frontend\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\FrontendInterface;
use App\Services\Contracts\MessageInterface;
use CoreConstants;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        try {
            // Validation for the incoming request parameters.
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'subject' => 'required',
                'body' => 'required',
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
