<?php
declare(strict_types=1);

class SignupController extends Controller
{
    public function index(): void
    {
        $this->render('signup', 'Inscription | VitaCalc', 'signup');
    }
}
