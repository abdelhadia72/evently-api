<?php

namespace App\Http\Controllers;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;

class UserController extends CrudController
{
    protected $table = 'users';

    protected $modelClass = User::class;

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return $this->modelClass;
    }

    public function createOne(Request $request)
    {
        try {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $request->merge([
                'password' => Hash::make($request->password),
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
                'is_verified' => false,
                'verified_at' => null,
                'login_attempts' => 0,
                'last_login_at' => null,
                'last_login_ip' => null,
            ]);

            return parent::createOne($request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.createOne : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterCreateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterCreateOne : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            if (isset($request->password) && ! empty($request->password)) {
                $request->merge(['password' => Hash::make($request->password)]);
            } else {
                $request->request->remove('password');
            }

            return parent::updateOne($id, $request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateOne : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterUpdateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterUpdateOne : '.$e->getMessage());
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
                return response()->json([
                    'success' => false,
                    'errors' => ['User not found'],
                ], 404);
            }

            if ($user->is_verified) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is already verified'],
                ], 400);
            }

            if ($user->otp !== $request->otp) {
                $user->increment('login_attempts');

                return response()->json([
                    'success' => false,
                    'errors' => ['Invalid OTP'],
                ], 400);
            }

            if ($user->otp_expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['OTP has expired'],
                ], 400);
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
                'message' => 'OTP verified successfully',
                'data' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.verifyOtp : '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')],
            ]);
        }
    }
}
