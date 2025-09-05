<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Xử lý lỗi 403 Forbidden
        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            return $this->render403Error($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render 403 Forbidden error page
     */
    protected function render403Error(Request $request, HttpException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện'
            ], 403);
        }

        return response()->view('errors.403', [
            'message' => 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện'
        ], 403);
    }
}
