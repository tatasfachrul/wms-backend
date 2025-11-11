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
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }
}
