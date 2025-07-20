<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

Route::get('/test-password-update', function() {
    // Find a test user (replace with actual test user email)
    $user = User::where('email', 'test@example.com')->first();
    
    if (!$user) {
        return "Test user not found. Please create a test user first.";
    }
    
    // Generate a new password
    $newPassword = 'test1234';
    $hashedPassword = Hash::make($newPassword);
    
    // Update the password
    $result = User::where('id', $user->id)->update([
        'password' => $hashedPassword
    ]);
    
    // Verify the update
    $updatedUser = User::find($user->id);
    $verify = Hash::check($newPassword, $updatedUser->password);
    
    return [
        'user_id' => $user->id,
        'old_password' => $user->password,
        'new_password' => $hashedPassword,
        'update_result' => $result,
        'verify_result' => $verify,
        'stored_hash' => $updatedUser->password,
        'message' => $verify ? 'Password updated successfully!' : 'Password update verification failed!'
    ];
});

// Test login with new password
Route::get('/test-login', function(Request $request) {
    $email = 'test@example.com';
    $password = 'test1234';
    
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        return "User not found";
    }
    
    $passwordMatch = Hash::check($password, $user->password);
    
    return [
        'user_found' => true,
        'password_match' => $passwordMatch,
        'user_id' => $user->id,
        'stored_hash' => $user->password,
        'message' => $passwordMatch ? 'Login successful!' : 'Invalid password'
    ];
});
