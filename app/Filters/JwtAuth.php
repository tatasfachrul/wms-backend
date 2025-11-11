<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = $request->getHeaderLine('Authorization');
        if (!$auth)
            return Services::response()->setStatusCode(401, 'Missing Authorization');

        if (strpos($auth, 'Bearer ') === 0)
            $token = substr($auth, 7);
        else
            return Services::response()->setStatusCode(401, 'Invalid Authorization');

        try {
            $key = getenv('JWT_SECRET') ?: 'supersecret';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            // attach user info to request? you can set via request attributes
            $request->user = $decoded;
        } catch (\Exception $e) {
            return Services::response()->setStatusCode(401, 'Invalid token: ' . $e->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
