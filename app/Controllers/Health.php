<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Health extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Service is healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
