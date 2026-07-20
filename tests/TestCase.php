<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function primeCaptcha(string $uri, string $key): string
    {
        $this->get($uri)->assertOk();

        return $this->captchaAnswer($key);
    }

    protected function captchaAnswer(string $key): string
    {
        return (string) session('form_captcha.'.$key.'.answer');
    }
}
