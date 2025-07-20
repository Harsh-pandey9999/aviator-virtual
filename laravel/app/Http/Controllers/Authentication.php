<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Hash;
use Illuminate\Http\Request;

class Authentication extends Controller
{
    public function login(Request $r)
    {
        try {
            \Log::info('=== LOGIN ATTEMPT ===');
            \Log::info('Login attempt with username/email: ' . $r->username);
            
            $validated = $r->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
            
            $data = "";
            $isSuccess = false;
            $message = "";
            
            // Find user by email or mobile
            $user = User::where('mobile', $r->username)
                      ->orWhere('email', $r->username)
                      ->first();
            
            if (!$user) {
                \Log::warning('Login failed: User not found - ' . $r->username);
                $response = [
                    'status' => 0, 
                    'title' => "Oops!!", 
                    'message' => "Username not exists!"
                ];
                return response()->json($response);
            }
            
            \Log::info('User found - ID: ' . $user->id . ', Email: ' . $user->email . ', Mobile: ' . $user->mobile);
            \Log::debug('User status: ' . $user->status);
            \Log::debug('Stored password hash: ' . $user->password);
            
            // Debug: Try to verify the password manually
            $password = $r->password;
            $hash = $user->password;
            $info = password_get_info($hash);
            
            \Log::debug('Password info:', [
                'algo' => $info['algoName'] ?? 'unknown',
                'algo_id' => $info['algo'] ?? 'unknown',
                'options' => $info['options'] ?? []
            ]);
            
            // Try different hashing methods if needed
            $passwordMatch = false;
            if (password_verify($password, $hash)) {
                $passwordMatch = true;
            } else if (md5($password) === $hash) {
                $passwordMatch = true;
                // Update to modern hashing if using old md5
                $user->password = Hash::make($password);
                $user->save();
                \Log::info('Updated password hash for user ID: ' . $user->id);
            }
            
            \Log::debug('Password verification:', [
                'input_password' => $password,
                'stored_hash' => $hash,
                'matches' => $passwordMatch ? 'Yes' : 'No',
                'md5_match' => (md5($password) === $hash) ? 'Yes' : 'No'
            ]);
            
            if (!$passwordMatch) {
                \Log::warning('Login failed: Incorrect password for user ID: ' . $user->id);
                $response = [
                    'status' => 0, 
                    'title' => "Oops!!", 
                    'message' => "Incorrect Password!"
                ];
                return response()->json($response);
            }
            
            // Check if user is active
            if (isset($user->status) && $user->status != '1' && $user->status != 'active') {
                \Log::warning('Login failed: Account is not active for user ID: ' . $user->id . ' Status: ' . $user->status);
                $response = [
                    'status' => 0, 
                    'title' => "Account Inactive", 
                    'message' => "Your account is not active. Please contact support."
                ];
                return response()->json($response);
            }
            
            // Regenerate session ID for security
            $r->session()->regenerate();
            
            // Store user data in session
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'isadmin' => $user->isadmin ?? false,
                'last_login' => now()
            ];
            
            $r->session()->put('userlogin', (object)$userData);
            
            // Update last login time (but don't fail login if this fails)
            try {
                $user->last_login = now();
                $user->save();
                \Log::info('Updated last login for user ID: ' . $user->id);
            } catch (\Exception $e) {
                \Log::warning('Could not update last login time: ' . $e->getMessage());
                // Continue with login even if last_login update fails
            }
            
            \Log::info('Login successful for user ID: ' . $user->id);
            
            $isSuccess = true;
            $message = "Login successful";
            
            // Prepare success response
            $response = [
                'status' => 1, 
                'title' => "Success!", 
                'message' => $message,
                'redirect' => url('/dashboard') // Redirect to dashboard after login
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 0,
                'title' => 'Error!',
                'message' => 'An error occurred during login. Please try again.'
            ]);
        }
    }

    public function register(Request $r)
    {
        $validated = $r->validate([
            'name' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);
        $data = "";
        $isSuccess = false;
        $message = "Something wen't wrong!";
        $promocode = '';
        if ($r->promocode != '') {
            $existpromocode = User::where('id', $r->promocode)->first();
            if ($existpromocode) {
                $olddata = User::where('email', $r->email)->orWhere('mobile', $r->mobile)->get();
                if (count($olddata) > 0) {
                    $message = "Dublicate Email Id/Mobile No., Please enter Unique Email id";
                } else {
                    $wallet = new Wallet;
                    $user = new User;
                    $user->name = $r->name;
					$user->image = "/images/avtar/av-".rand(1,72).".png";
                    $user->mobile = $r->mobile;
                    $user->email = $r->email;
                    $user->password = Hash::make($r->password);
                    $user->currency = '₹';
                    $user->gender = $r->gender;
                    $user->country = 'IN';
                    $user->status = '1';
                    $user->promocode = $r->promocode;
                    if ($user->save()) {
                        $afterregisterdata = User::where('email', $r->email)->orderBy('id', 'desc')->first();
                        if ($afterregisterdata) {
                            $wallet->userid = $afterregisterdata->id;
                            $wallet->amount = setting('initial_bonus');
                            if ($wallet->save()) {
                                $data = array("username" => $afterregisterdata->email, "password" => $r->password, "token" => csrf_token());
                                $isSuccess = true;
                            }
                        }
                    }
                }
            }else{
                $data = array();
                $message = "Invalid Promocode";
            }
        } else {
            $olddata = User::where('email', $r->email)->orWhere('mobile', $r->mobile)->get();
            if (count($olddata) > 0) {
                $message = "Dublicate Email Id/Mobile No., Please enter Unique Email id";
            } else {
                $wallet = new Wallet;
                $user = new User;
                $user->name = $r->name;
                $user->mobile = $r->mobile;
                $user->email = $r->email;
                $user->password = Hash::make($r->password);
                $user->currency = '₹';
                $user->gender = $r->gender;
                $user->country = 'IN';
                $user->status = '1'; // Active status
                $user->promocode = $r->promocode;
                
                \Log::info('New user registration:', [
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'status' => $user->status,
                    'password_hash' => $user->password
                ]);
                if ($user->save()) {
                    $afterregisterdata = User::where('email', $r->email)->orderBy('id', 'desc')->first();
                    if ($afterregisterdata) {
                        $wallet->userid = $afterregisterdata->id;
                        $wallet->amount = setting('initial_bonus');
                        if ($wallet->save()) {
                            $data = array("username" => $afterregisterdata->email, "password" => $r->password, "token" => csrf_token());
                            $isSuccess = true;
                        }
                    }
                }
            }
        }
        $res = array("data" => $data, "isSuccess" => $isSuccess, "message" => $message);
        return response()->json($res);
    }

    public function adminlogin(Request $r)
    {
        $validated = $r->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $response = array('status' => 0, 'title' => "Oops!!", 'message' => "Invalid Credential!");
        $usernameexist = User::where('mobile', $r->username)->orWhere('email', $r->username)->where('isadmin', '1')->first();
        if ($usernameexist) {
            if (Hash::check($r->password, $usernameexist->password)) {
                $r->session()->put('adminlogin', $usernameexist);
                $response = array('status' => 1, 'title' => "Success!!", 'message' => "Login Successfully!");
            } else {
                $response = array('status' => 0, 'title' => "Oops!!", 'message' => "Incorrect Password!");
            }
        } else {
            $response = array('status' => 0, 'title' => "Oops!!", 'message' => "Username not exists!");
        }
        return response()->json($response);
    }

    public function updatePassword(Request $request)
    {
        // Enable query logging
        \DB::enableQueryLog();
        
        // Debug logging
        \Log::info('=== PASSWORD UPDATE REQUEST START ===');
        \Log::info('Request data: ' . json_encode($request->all()));
        \Log::info('Session data: ' . json_encode(session()->all()));
        \Log::info('Headers: ' . json_encode($request->headers->all()));
        
        try {
            // Get user from session
            $userSession = session('userlogin');
            
            if (!$userSession) {
                \Log::error('User session not found');
                return response()->json([
                    'status' => 0,
                    'title' => 'Session Expired',
                    'message' => 'Your session has expired. Please log in again.'
                ], 401);
            }
            
            // Get user from database
            $user = User::find($userSession->id);
            
            if (!$user) {
                \Log::error('User not found in database. User ID: ' . ($userSession->id ?? 'null'));
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'User not found. Please log in again.'
                ], 401);
            }
            
            // Debug logging
            \Log::info('User found - ID: ' . $user->id . ', Email: ' . $user->email);
            \Log::info('Stored password hash: ' . $user->password);
            \Log::info('Provided current password: ' . $request->current_password);
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                \Log::error('Current password does not match for user ID: ' . $user->id);
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'The current password is incorrect.'
                ], 422);
            }
            
            // Validate new password
            if (strlen($request->new_password) < 6) {
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'New password must be at least 6 characters.'
                ], 422);
            }
            
            if ($request->new_password !== $request->new_password_confirmation) {
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'New password and confirmation do not match.'
                ], 422);
            }
            
            if ($request->current_password === $request->new_password) {
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'New password must be different from current password.'
                ], 422);
            }

            // Generate new password hash
            $newHashedPassword = Hash::make($request->new_password);
            \Log::info('New password hash generated: ' . $newHashedPassword);
            
            // Update password directly in the database
            $updateResult = User::where('id', $user->id)
                ->update([
                    'password' => $newHashedPassword,
                    'updated_at' => now()
                ]);
            
            // Log the executed query
            $queries = \DB::getQueryLog();
            \Log::info('Update Query: ' . json_encode(end($queries)));
            
            if ($updateResult) {
                // Get the updated user
                $updatedUser = User::find($user->id);
                
                // Verify the password was actually updated
                $verifyHash = Hash::check($request->new_password, $updatedUser->password);
                
                if ($verifyHash) {
                    // Update session
                    $request->session()->put('userlogin', $updatedUser);
                    
                    \Log::info('Password updated successfully for user ID: ' . $user->id);
                    \Log::info('=== PASSWORD UPDATE REQUEST COMPLETED SUCCESSFULLY ===');
                    
                    return response()->json([
                        'status' => 1,
                        'title' => 'Success',
                        'message' => 'Password updated successfully!',
                        'redirect' => url('/dashboard')
                    ]);
                } else {
                    \Log::error('Password verification failed after update for user ID: ' . $user->id);
                    \Log::error('Expected hash to verify: ' . $newHashedPassword);
                    \Log::error('Actual hash in database: ' . ($updatedUser->password ?? 'null'));
                    
                    return response()->json([
                        'status' => 0,
                        'title' => 'Error',
                        'message' => 'Password update verification failed. Please contact support.'
                    ], 500);
                }
            } else {
                \Log::error('Failed to update password in database for user ID: ' . $user->id);
                return response()->json([
                    'status' => 0,
                    'title' => 'Error',
                    'message' => 'Failed to update password. Please try again.'
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error updating password: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== PASSWORD UPDATE REQUEST FAILED ===');
            
            return response()->json([
                'status' => 0,
                'title' => 'Error',
                'message' => 'An error occurred while updating your password. Please try again.'
            ], 500);
        } finally {
            // Disable query logging
            \DB::disableQueryLog();
        }
    }
}
