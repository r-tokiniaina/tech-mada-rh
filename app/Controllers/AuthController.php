<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function login()
    {
        $user = session()->get('user');
        if ($user !== null) {
            return redirect()->to($user['role']);
        }

        return view('login');
    }

    public function postLogin()
    {
        $email = $this->request->getPost('email') ?? '';
        $password = $this->request->getPost('password') ?? '';

        $user = model('EmployeModel')->findByEmailAndPassword($email, $password);
        if ($user === null) {
            return redirect()->back()->with('error', 'Identifiants incorrects. Veuillez réessayer.');
        }

        session()->set('user', $user);
        return redirect()->to($user['role']);
    }

    public function logout()
    {
        session()->remove('user');
        return redirect()->to('login');
    }
}
