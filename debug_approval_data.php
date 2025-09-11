<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ApprovalRequest;
use App\Models\ApprovalForm;
use App\Models\User;

echo "=== DEBUG APPROVAL DATA ===\n";
echo "ApprovalRequest count: " . ApprovalRequest::count() . "\n";
echo "ApprovalForm count: " . ApprovalForm::count() . "\n";
echo "User count: " . User::count() . "\n\n";

echo "=== APPROVAL REQUESTS ===\n";
$requests = ApprovalRequest::with(['creator', 'currentApprover', 'approvalForm'])->get();
foreach($requests as $request) {
    echo "ID: {$request->id}\n";
    echo "  Form Type: {$request->form_type}\n";
    echo "  Status: {$request->approval_status}\n";
    echo "  Creator: " . ($request->creator ? $request->creator->name : 'NULL') . "\n";
    echo "  Current Approver: " . ($request->currentApprover ? $request->currentApprover->name : 'NULL') . "\n";
    echo "  Created: {$request->created_at}\n";
    echo "  Form Data: " . json_encode($request->form_data) . "\n\n";
}

echo "=== APPROVAL FORMS ===\n";
$forms = ApprovalForm::all();
foreach($forms as $form) {
    echo "ID: {$form->id}\n";
    echo "  Form Type: {$form->form_type}\n";
    echo "  Form Name: {$form->form_name}\n";
    echo "  Active: " . ($form->is_active ? 'Yes' : 'No') . "\n\n";
}
