<?php

declare(strict_types=1);


beforeEach(function () {
    // Overwrite core services if needed
    // Craft::$app->set('foo', new \my\FooDummy());
});

it('is always true', function () {

    $someResult = true;

    // Assert
    expect($someResult)->toBeTrue();
});
