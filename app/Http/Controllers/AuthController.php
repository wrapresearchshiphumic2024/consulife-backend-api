<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'data' => null,
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function Register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string',
            'gender' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        } else {
            $profile_picture = null;
        }

        $user = new User();
        $user->id = (string) Str::uuid();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->role = 'patient';
        $user->gender = $request->gender;
        $user->profile_picture = $profile_picture;
        $user->save();

        Patient::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
        ]);
    }

    public function RegisterPsychologist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string',
            'gender' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'degree' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'university' => 'required|string|max:255',
            'graduation_year' => 'required|digits:4',
            'language' => 'required|array',
            'certification' => 'required|array',
            'certification.*' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'specialization' => 'required|array',
            'work_experience' => 'required|string',
            'profesional_identification_number' => 'required|string|max:255',
            'cv' => 'required|array',
            'cv.*' => 'required|file|mimes:pdf|max:2048',
            'practice_license' => 'required|array',
            'practice_license.*' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $certificationPaths = [];
        if ($request->hasFile('certification')) {
            foreach ($request->file('certification') as $file) {
                $certificationPaths[] = 'storage/' . $file->store('certifications', 'public');
            }
        }

        $cvPaths = [];
        if ($request->hasFile('cv')) {
            foreach ($request->file('cv') as $file) {
                $cvPaths[] = 'storage/' . $file->store('cvs', 'public');
            }
        }

        $practiceLicensePaths = [];
        if ($request->hasFile('practice_license')) {
            foreach ($request->file('practice_license') as $file) {
                $practiceLicensePaths[] = 'storage/' . $file->store('licenses', 'public');
            }
        }

        if ($request->hasFile('profile_picture')) {
            $profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        } else {
            $profile_picture = null;
        }

        $user = new User();
        $user->id = (string) Str::uuid();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->role = 'psychologists';
        $user->gender = $request->gender;
        $user->profile_picture = $profile_picture;
        $user->save();

        $user_id = $user->id;

        Psychologist::create([
            'user_id' => $user_id,
            'degree' => $request->degree,
            'major' => $request->major,
            'university' => $request->university,
            'graduation_year' => $request->graduation_year,
            'language' => json_encode($request->language),
            'certification' => json_encode($certificationPaths),
            'specialization' => json_encode($request->specialization),
            'work_experience' => $request->work_experience,
            'profesional_identification_number' => $request->profesional_identification_number,
            'cv' => json_encode($cvPaths),
            'practice_license' => json_encode($practiceLicensePaths),
            'is_verified' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist created successfully'
        ], 201);
    }

    public function Profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User details',
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful',
        ]);
    }
}
