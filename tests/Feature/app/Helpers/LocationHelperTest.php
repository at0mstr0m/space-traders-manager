<?php

declare(strict_types=1);

namespace Tests\Unit\App\Helpers;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
