<?php

namespace App\Http\Controllers;

use App\Enums\ROLE;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\OTPVerificationNotification;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }
            $admin = $request->input('admin');
            if ($admin && ! $user->hasRole(ROLE::ADMIN)) {
                return response()->json(['success' => false, 'errors' => [__('auth.not_admin')]]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.me: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->input('email'))->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return response()->json(['success' => false, 'errors' => [__('auth.failed')]]);
            }
            $admin = $request->input('admin');
            if ($admin && ! $user->hasRole(ROLE::ADMIN)) {
                return response()->json(['success' => false, 'errors' => [__('auth.not_admin')]]);
            }
            $token = $user->createToken('authToken', ['expires_in' => 60 * 24 * 30])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => __('auth.login_success'),
                'data' => ['token' => $token],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.login: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    return response()->json([
                        'success' => false,
                        'errors' => [__('auth.email_already_exists')],
                    ]);
                }
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_verified' => false,
                    'otp' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                ]);

                $role = ROLE::from($request->role);
                $user->assignRole($role);

                $token = $user->createToken('authToken', ['expires_in' => 60 * 24 * 30])->plainTextToken;
                $user->notify(new OTPVerificationNotification($otp));

                return response()->json([
                    'success' => true,
                    'data' => [
                        'token' => $token,
                        'user' => $user,
                    ],
                    'message' => 'Registration successful',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.register: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->tokens()->delete();

            return response()->json(['success' => true, 'message' => __('auth.logout_success')]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.logout: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function requestPasswordReset(Request $request)
    {
        try {
            $email = $request->email;
            $status = Password::sendResetLink(['email' => $email]);
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => __('auth.password_reset_link_sent'),
                ]);
            } elseif ($status === Password::INVALID_USER) {
                return response()->json(['success' => false, 'errors' => [__('users.not_found')]]);
            } elseif ($status === Password::INVALID_TOKEN) {
                return response()->json(['success' => false, 'errors' => [__('auth.invalid_token')]]);
            } elseif ($status === Password::RESET_THROTTLED) {
                return response()->json(['success' => false, 'errors' => [__('auth.reset_throttled')]]);
            }

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        } catch (\Exception $e) {
            Log::error(
                'Error caught in function AuthController.requestPasswordReset: '.$e->getMessage()
            );
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $status = Password::reset(
                    $request->only('email', 'password', 'password_confirmation', 'token'),
                    function ($user, $password) {
                        $user->password = Hash::make($password);
                        $user->save();
                    }
                );
                if ($status === Password::PASSWORD_RESET) {
                    return response()->json([
                        'success' => true,
                        'message' => __('auth.password_reset_success'),
                    ]);
                } elseif ($status === Password::INVALID_USER) {
                    return response()->json(['success' => false, 'errors' => [__('users.not_found')]]);
                } elseif ($status === Password::INVALID_TOKEN) {
                    return response()->json(['success' => false, 'errors' => [__('auth.invalid_token')]]);
                } elseif ($status === Password::RESET_THROTTLED) {
                    return response()->json(['success' => false, 'errors' => [__('auth.reset_throttled')]]);
                }

                return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
            });
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.resetPassword: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string|size:6',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => 'User not found',
                    ],
                    404
                );
            }

            if ($user->is_verified) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => 'User is already verified',
                    ],
                    400
                );
            }

            if ($user->otp !== $request->otp) {
                $user->increment('login_attempts');

                return response()->json(
                    [
                        'success' => false,
                        'errors' => 'Invalid OTP',
                    ],
                    400
                );
            }

            if ($user->otp_expires_at < now()) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => 'OTP has expired',
                    ],
                    400
                );
            }

            $user->update([
                'is_verified' => true,
                'verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null,
                'login_attempts' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your Acccount Have Been verified successfully',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.verifyOtp : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')],
            ]);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => ['User not found'],
                    ],
                    404
                );
            }

            if ($user->is_verified) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => ['User is already verified'],
                    ],
                    400
                );
            }

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
                'login_attempts' => 0,
            ]);

            $user->notify(new OTPVerificationNotification($otp));

            return response()->json([
                'success' => true,
                'message' => 'A new verification code has been sent to your email',
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.resendOtp: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ]);
        }
    }
}
