<?php

require_once 'vendor/autoload.php';

use App\Models\ApprovalForm;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECK APPROVAL FORMS ===\n\n";

$forms = ApprovalForm::all();
foreach($forms as $form) {
    echo "ID: {$form->id}\n";
    echo "Type: {$form->form_type}\n";
    echo "Active: " . ($form->is_active ? 'Yes' : 'No') . "\n";
    echo "Form fields:\n";
    echo json_encode($form->form_fields, JSON_PRETTY_PRINT) . "\n\n";
}

echo "✅ Check hoàn thành!\n";
