<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use Firebase\JWT\JWT;

class AuthController extends ResourceController
{
    protected $modelName = UserModel::class;
    protected $format = 'json';

    public function login()
    {
        $payload = $this->request->getJSON(true);
        if (!$payload || empty($payload['email']) || empty($payload['password'])) {
            return $this->failValidationError('email and password required');
        }
        $model = new UserModel();
        $user = $model->where('email', $payload['email'])->first();
        if (!$user)
            return $this->failNotFound('User not found');

        if (!password_verify($payload['password'], $user['password'])) {
            return $this->failUnauthorized('Invalid credentials');
        }

        $key = getenv('JWT_SECRET') ?: 'supersecret';
        $iat = time();
        $exp = $iat + 3600 * 24;
        $token = JWT::encode(['iat' => $iat, 'exp' => $exp, 'sub' => $user['id'], 'role' => $user['role']], $key, 'HS256');

        return $this->respond([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]
            
        ]);
    }

    public function register()
    {
        $payload = $this->request->getJSON(true);
        if (!$payload || empty($payload['email']) || empty($payload['password']) || empty($payload['name'])) {
            return $this->failValidationError('name, email, and password are required');
        }

        $model = new UserModel();

        // check if email exists
        if ($model->where('email', $payload['email'])->first()) {
            return $this->failResourceExists('Email already registered');
        }

        $data = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'role' => $payload['role'] ?? 'staff'
        ];

        $model->insert($data);

        return $this->respondCreated([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role']
            ]
        ]);
    }

    //
    public function profile()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->failUnauthorized('Missing or invalid Authorization header');
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $key = getenv('JWT_SECRET') ?: 'supersecret';

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid or expired token');
        }

        $userId = $decoded->sub ?? null;
        if (!$userId) {
            return $this->failUnauthorized('Invalid token payload');
        }

        $model = new UserModel();
        $user = $model->find($userId);
        if (!$user) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
    }
}
