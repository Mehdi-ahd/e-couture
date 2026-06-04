<?php

use App\DTO\Scan\PatternScanOptions;

describe('PatternScanOptions', function () {

    it('omits crop from remove.bg body when crop is false', function () {
        $options = new PatternScanOptions(crop: false);
        $body = $options->toRemoveBgBody();

        expect($body)->not->toHaveKey('crop');
    });

    it('includes crop in remove.bg body when crop is true', function () {
        $options = new PatternScanOptions(crop: true);
        $body = $options->toRemoveBgBody();

        expect($body)->toHaveKey('crop');
        expect($body['crop'])->toBe('1');
    });

    it('includes crop_margin only when crop is true and margin is set', function () {
        $options = new PatternScanOptions(crop: true, cropMargin: '5%');
        $body = $options->toRemoveBgBody();

        expect($body)->toHaveKey('crop_margin');
        expect($body['crop_margin'])->toBe('5%');
    });

    it('omits crop_margin when crop is false even if margin is set', function () {
        $options = new PatternScanOptions(crop: false, cropMargin: '5%');
        $body = $options->toRemoveBgBody();

        expect($body)->not->toHaveKey('crop');
        expect($body)->not->toHaveKey('crop_margin');
    });

    it('sets default body parameters correctly', function () {
        $options = new PatternScanOptions;
        $body = $options->toRemoveBgBody();

        expect($body['size'])->toBe('preview');
        expect($body['format'])->toBe('png');
        expect($body['channels'])->toBe('rgba');
        expect($body['type'])->toBe('auto');
        expect($body)->not->toHaveKey('crop');
    });

    it('uses the provided remove_bg_size', function () {
        $options = new PatternScanOptions(removeBgSize: 'hd');
        $body = $options->toRemoveBgBody();

        expect($body['size'])->toBe('hd');
    });

});
