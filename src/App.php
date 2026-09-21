<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;

class App
{
    public function build(): PHPMailer
    {
        return new PHPMailer(true);
    }
}
