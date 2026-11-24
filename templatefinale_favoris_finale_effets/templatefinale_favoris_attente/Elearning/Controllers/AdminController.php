<?php
declare(strict_types=1);

class AdminController extends Controller
{
    public function index(): void
    {
        $this->render('admin', 'Administration | VitaCalc', 'dashboard', 'admin');
    }
}
