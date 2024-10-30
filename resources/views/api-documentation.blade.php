<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Documentation</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
    }

    .container {
      width: 80%;
      margin: auto;
      padding: 20px;
    }

    h2 {
      color: #333;
    }

    .endpoint {
      margin-bottom: 20px;
    }

    pre {
      background-color: #f4f4f4;
      padding: 10px;
      border: 1px solid #ddd;
      overflow-x: auto;
    }

    .code {
      background-color: #eee;
      padding: 2px 5px;
      border-radius: 4px;
      color: #d14;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>API Documentation (Updated on 30/10/2024)</h1>

    <div class="endpoint">
      <h2>Admin Dashboard Data - <span class="code">GET /api/admin/dashboard</span></h2>
      <p>Returns statistics for the admin dashboard.</p>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Admin dashboard data",
    "data": {
        "psychologists": 10,
        "total_patient": 100,
        "ongoing_appointments": 5,
        "completed_appointments": 50
    }
}</code></pre>
    </div>

    <div class="endpoint">
      <h2>Daftar Psikolog Terverifikasi - <span class="code">GET /api/psychologists/verified</span></h2>
      <p>Fetches the list of verified psychologists.</p>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "List of verified psychologists",
    "data": [
        {
            "id": 1,
            "user_id": 2,
            "profile_picture": "url_to_profile_picture",
            "firstname": "John",
            "lastname": "Doe",
            "gender": "male",
            "profesional_identification_number": "12345",
            "degree": "PhD",
            "specialization": "Clinical Psychology",
            "work_experience": 10,
            "is_verified": true,
            "detail_url": "api/psychologists/1/detail"
        }
    ]
}</code></pre>
    </div>

    <div class="endpoint">
      <h2>Daftar Psikolog Belum Terverifikasi - <span class="code">GET /api/psychologists/not-verified</span></h2>
      <p>Fetches the list of unverified psychologists.</p>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "List of unverified psychologists",
    "data": [
        {
            "id": 1,
            "user_id": 2,
            "profile_picture": "url_to_profile_picture",
            "firstname": "Jane",
            "lastname": "Smith",
            "profesional_identification_number": "67890",
            "degree": "MSc",
            "specialization": "Counseling",
            "work_experience": 5,
            "is_verified": false,
            "is_rejected": false,
            "approve_url": "/api/psychologists/2/approve",
            "reject_url": "/api/psychologists/2/reject",
            "detail_url": "/api/psychologists/2/detail"
        }
    ]
}</code></pre>
    </div>

    <div class="endpoint">
      <h2>Approve Psikolog - <span class="code">POST /api/psychologists/{id}/approve</span></h2>
      <p>Approves a psychologist by ID.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>id</strong>: string (Psychologist's ID)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Psychologist approved successfully",
    "data": {
        "id": 1,
        "is_verified": true
    }
}</code></pre>
      <h3>Error Response:</h3>
      <pre><code>{
    "status": "error",
    "message": "Psychologist not found"
}</code></pre>
    </div>

    <div class="endpoint">
      <h2>Reject Psikolog - <span class="code">POST /api/psychologists/{id}/reject</span></h2>
      <p>Rejects a psychologist by ID.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>id</strong>: string (Psychologist's ID)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Psychologist rejected successfully",
    "data": {
        "id": 1,
        "is_verified": false,
        "is_rejected": true
    }
}</code></pre>
      <h3>Error Response:</h3>
      <pre><code>{
    "status": "error",
    "message": "Psychologist not found"
}</code></pre>
    </div>

    <div class="endpoint">
      <h2>Detail Psikolog - <span class="code">GET /api/psychologists/{id}/detail</span></h2>
      <p>Fetches details of a psychologist by ID.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>id</strong>: string (User ID of psychologist)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Psychologist details",
    "data": {
        "id": 1,
        "user_id": 2,
        "profile_picture": "url_to_profile_picture",
        "firstname": "John",
        "lastname": "Doe",
        "gender": "male",
        "profesional_identification_number": "12345",
        "degree": "PhD",
        "specialization": "Clinical Psychology",
        "work_experience": 10,
        "is_verified": true,
        "approve_url": "/api/psychologists/2/approve",
        "reject_url": "/api/psychologists/2/reject"
    }
}</code></pre>
      <h3>Error Response:</h3>
      <pre><code>{
    "status": "error",
    "message": "Psychologist not found"
}</code></pre>
    </div>
    <div class="endpoint">
      <h2>Login - <span class="code">POST /api/login</span></h2>
      <p>Authenticates a user and returns an access token.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>email</strong>: string (required)</li>
        <li><strong>password</strong>: string (required)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user_id": "user-id",
        "access_token": "token",
        "token_type": "Bearer",
        "role": "patient"
    }
}</code></pre>
    </div>

    <!-- Register Endpoint -->
    <div class="endpoint">
      <h2>Register - <span class="code">POST /api/register</span></h2>
      <p>Registers a new patient user.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>firstname</strong>: string (required)</li>
        <li><strong>lastname</strong>: string (required)</li>
        <li><strong>email</strong>: string (required, unique)</li>
        <li><strong>password</strong>: string (required, min: 8)</li>
        <li><strong>phone_number</strong>: string (required)</li>
        <li><strong>gender</strong>: string (required)</li>
        <li><strong>profile_picture</strong>: file (optional, image)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "User created successfully"
}</code></pre>
    </div>

    <!-- Register Psychologist Endpoint -->
    <div class="endpoint">
      <h2>Register Psychologist - <span class="code">POST /api/register/psychologist</span></h2>
      <p>Registers a new psychologist.</p>
      <h3>Parameters:</h3>
      <ul>
        <li><strong>firstname</strong>, <strong>lastname</strong>, <strong>email</strong>, <strong>password</strong>: Required fields similar to patient registration.</li>
        <li><strong>degree</strong>: string (required)</li>
        <li><strong>major</strong>: string (required)</li>
        <li><strong>university</strong>: string (required)</li>
        <li><strong>graduation_year</strong>: string (required, digits: 4)</li>
        <li><strong>language</strong>: array (required)</li>
        <li><strong>certification</strong>: array of files (required, pdf/image)</li>
        <li><strong>specialization</strong>: array (required)</li>
        <li><strong>work_experience</strong>: string (required)</li>
        <li><strong>profesional_identification_number</strong>: string (required)</li>
        <li><strong>cv</strong>: array of files (required, pdf)</li>
        <li><strong>practice_license</strong>: array of files (required, pdf/image)</li>
      </ul>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Psychologist created successfully"
}</code></pre>
    </div>

    <!-- Profile Endpoint -->
    <div class="endpoint">
      <h2>Profile - <span class="code">GET /api/profile</span></h2>
      <p>Fetches the authenticated user's profile data.</p>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "User details",
    "data": {
        "user_id": "user-id",
        "profile_picture": "url_to_profile_picture",
        "firstname": "John",
        "lastname": "Doe",
        "email": "johndoe@example.com",
        "phone_number": "123456789",
        "role": "psychologist",
        // Other profile fields depending on role
    }
}</code></pre>
    </div>

    <!-- Logout Endpoint -->
    <div class="endpoint">
      <h2>Logout - <span class="code">POST /api/logout</span></h2>
      <p>Logs out the authenticated user and deletes the access token.</p>
      <h3>Response:</h3>
      <pre><code>{
    "status": "success",
    "message": "Logout successful"
}</code></pre>
    </div>
  </div>
</body>

</html>