<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ApprovalRequest;
use App\Models\ApprovalForm;
use App\Models\User;

echo "=== CREATING TEST DATA ===\n";

// Get first user
$user = User::first();
if (!$user) {
    echo "No users found! Please create a user first.\n";
    exit;
}

echo "Using user: {$user->name} (ID: {$user->id})\n";

// Create test approval request
$request = ApprovalRequest::create([
    'form_type' => 'payment_request',
    'form_data' => [
        'title' => 'Test Payment Request',
        'amount' => 1000000,
        'description' => 'Test payment request for debugging'
    ],
    'approval_status' => 'pending',
    'created_by_id' => $user->id,
    'current_approver_id' => $user->id
]);

echo "Created approval request ID: {$request->id}\n";
echo "Status: {$request->approval_status}\n";
echo "Creator: {$request->creator->name}\n";

// Check counts
echo "\n=== FINAL COUNTS ===\n";
echo "Total ApprovalRequests: " . ApprovalRequest::count() . "\n";
echo "User's requests: " . ApprovalRequest::where('created_by_id', $user->id)->count() . "\n";
echo "Pending requests: " . ApprovalRequest::where('approval_status', 'pending')->count() . "\n";
