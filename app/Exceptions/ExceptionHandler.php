<?php

namespace App\Exceptions;

use Log;
use App\Http\Controllers\ExceptionHandlerController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request as RequestFC;

class ExceptionHandler extends Exception
{

    protected $data = [];
    protected $http_status;
    public function __construct($error)
    {
        $validation_exception_type = 'Illuminate\Validation\ValidationException';
        $this->http_status = get_class($error) == $validation_exception_type ? 422 : 500;
       
        $this->data = [
            'success' => false,
            'message' => $this->http_status == 422 ? $error->getMessage() : 'SERVER ERROR FOUND, REPORT TO DEVELOPER',
            'debug_trace' =>  [
                "path_info" => RequestFC::getpathInfo(),
                "path" => RequestFC::path(),
                "full_url" => RequestFC::fullUrl(),
                "acceptable_content_type" => RequestFC::getAcceptableContentTypes(),
                "queryies" => RequestFC::query(),
                "action_controller" => RequestFC::route()?->getAction()['controller'] ?? "System",
                "action_path" => RequestFC::route()?->getAction()['as'] ?? "System",
                'exception_message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ],
        ];
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        if($this->http_status != 422){
            Log::emergency($this->data);
            // (new ExceptionHandlerController())->store(new Request($this->data));
        }
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request)
    {
        return Response::json($this->data, $this->http_status);
    }
}
