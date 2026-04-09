<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('school')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token', ['*'], now()->addDay())->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()->load('school')));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'first_name'    => 'sometimes|string|max:100',
            'last_name'     => 'sometimes|string|max:100',
            'phone'         => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'gender'        => 'sometimes|in:male,female,other',
            'address'       => 'sometimes|string|max:255',
        ]);

        $user->update($data);
        return response()->json($this->formatUser($user->fresh()->load('school')));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        // In production: send password reset email
        return response()->json(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        // In production: validate token and reset password
        return response()->json(['message' => 'Password reset successfully.']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->first_name . ' ' . $user->last_name,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'role'          => $user->role,
            'avatar'        => $user->avatar,
            'initials'      => strtoupper(substr($user->first_name,0,1).substr($user->last_name,0,1)),
            'gender'        => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'address'       => $user->address,
            'is_active'     => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'school_id'     => $user->school_id,
            'school'        => $user->school ? [
                'id'              => $user->school->id,
                'name'            => $user->school->name,
                'current_session' => $user->school->current_session,
                'current_term'    => $user->school->current_term,
                'plan'            => $user->school->plan,
            ] : null,
        ];
    }
}
