<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Barryvdh\DomPDF\PDF;

class PDFController extends Controller
{
    public function preview($approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        $formConfig = $approvalRequest->getFormConfig();
        
        if (!$formConfig) {
            abort(404, 'Không tìm thấy cấu hình form');
        }
        
        return view('pdf.preview', [
            'request' => $approvalRequest,
            'formConfig' => $formConfig,
            'formData' => $approvalRequest->form_data
        ]);
    }

    public function generatePDF($approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        $formConfig = $approvalRequest->getFormConfig();
        
        if (!$formConfig) {
            abort(404, 'Không tìm thấy cấu hình form');
        }
        
        $pdf = PDF::loadView('pdf.dynamic-form', [
            'request' => $approvalRequest,
            'formConfig' => $formConfig,
            'formData' => $approvalRequest->form_data
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream($formConfig->form_name . '-' . $approvalRequestId . '.pdf');
    }

    public function print($approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        $formConfig = $approvalRequest->getFormConfig();
        
        if (!$formConfig) {
            abort(404, 'Không tìm thấy cấu hình form');
        }
        
        return view('pdf.print', [
            'request' => $approvalRequest,
            'formConfig' => $formConfig,
            'formData' => $approvalRequest->form_data
        ]);
    }
}