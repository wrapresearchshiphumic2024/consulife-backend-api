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
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found',
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user->id, 
                'access_token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->role,
            ],
        ]);
    }

    public function Register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string',
            'gender' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
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
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
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

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = new User();
        $user->id = (string) Str::uuid();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->role = 'psychologist';
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
        $user = $request->user();

        if ($user->role === 'psychologist') {
            $psychologist = $user->psychologist()->with('user')->first();
    
            if ($psychologist) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Psychologist details',
                    'data' => [
                        'user_id' => $user->id,
                        'profile_picture' => $user->profile_picture,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'degree' => $psychologist->degree,
                        'major' => $psychologist->major,
                        'university' => $psychologist->university,
                        'graduation_year' => $psychologist->graduation_year,
                        'language' => $psychologist->language,
                        'certification' => $psychologist->certification,
                        'specialization' => $psychologist->specialization,
                        'work_experience' => $psychologist->work_experience,
                        'profesional_identification_number' => $psychologist->profesional_identification_number,
                        'cv' => $psychologist->cv,
                        'practice_license' => $psychologist->practice_license,
                        'is_verified' => $psychologist->is_verified,
                    ],
                ]);
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'User details',
            'data' => $user,
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
