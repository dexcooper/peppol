<?php

use App\Enums\VatRate;

test('has zero percent vat rate', function () {
    expect(VatRate::Zero->value)->toBe(0);
});

test('has six percent vat rate', function () {
    expect(VatRate::Six->value)->toBe(6);
});

test('has twelve percent vat rate', function () {
    expect(VatRate::Twelve->value)->toBe(12);
});

test('has twenty one percent vat rate', function () {
    expect(VatRate::TwentyOne->value)->toBe(21);
});

test('zero percent has correct label', function () {
    expect(VatRate::Zero->label())->toBe('0%');
});

test('six percent has correct label', function () {
    expect(VatRate::Six->label())->toBe('6%');
});

test('twelve percent has correct label', function () {
    expect(VatRate::Twelve->label())->toBe('12%');
});

test('twenty one percent has correct label', function () {
    expect(VatRate::TwentyOne->label())->toBe('21%');
});

test('has all expected vat rates', function () {
    $cases = VatRate::cases();

    expect($cases)->toHaveCount(4);
    expect($cases)->toContain(VatRate::Zero);
    expect($cases)->toContain(VatRate::Six);
    expect($cases)->toContain(VatRate::Twelve);
    expect($cases)->toContain(VatRate::TwentyOne);
});

test('can get vat rate by value', function () {
    expect(VatRate::from(0))->toBe(VatRate::Zero);
    expect(VatRate::from(6))->toBe(VatRate::Six);
    expect(VatRate::from(12))->toBe(VatRate::Twelve);
    expect(VatRate::from(21))->toBe(VatRate::TwentyOne);
});

test('throws exception for invalid vat rate', function () {
    expect(fn() => VatRate::from(15))
        ->toThrow(ValueError::class);
});

test('can try to get vat rate by value', function () {
    expect(VatRate::tryFrom(0))->toBe(VatRate::Zero);
    expect(VatRate::tryFrom(21))->toBe(VatRate::TwentyOne);
    expect(VatRate::tryFrom(15))->toBeNull();
    expect(VatRate::tryFrom(100))->toBeNull();
});

test('enum arrayable trait provides values method', function () {
    $values = VatRate::values();

    expect($values)->toBeArray();
    expect($values)->toContain(0);
    expect($values)->toContain(6);
    expect($values)->toContain(12);
    expect($values)->toContain(21);
});

test('vat rates are ordered from low to high', function () {
    $values = VatRate::values();

    expect($values[0])->toBe(0);
    expect($values[1])->toBe(6);
    expect($values[2])->toBe(12);
    expect($values[3])->toBe(21);
});

test('can calculate vat amount for each rate', function () {
    $baseAmount = 10000; // €100.00

    $vatZero = ($baseAmount * VatRate::Zero->value) / 100;
    expect($vatZero)->toBe(0);

    $vatSix = ($baseAmount * VatRate::Six->value) / 100;
    expect($vatSix)->toBe(600); // €6.00

    $vatTwelve = ($baseAmount * VatRate::Twelve->value) / 100;
    expect($vatTwelve)->toBe(1200); // €12.00

    $vatTwentyOne = ($baseAmount * VatRate::TwentyOne->value) / 100;
    expect($vatTwentyOne)->toBe(2100); // €21.00
});

test('is backed by integer', function () {
    expect(VatRate::Zero->value)->toBeInt();
    expect(VatRate::Six->value)->toBeInt();
    expect(VatRate::Twelve->value)->toBeInt();
    expect(VatRate::TwentyOne->value)->toBeInt();
});

test('standard dutch vat rate is twenty one percent', function () {
    // Het standaard BTW-tarief in Nederland is 21%
    expect(VatRate::TwentyOne->value)->toBe(21);
    expect(VatRate::TwentyOne->label())->toBe('21%');
});

test('reduced dutch vat rate is six percent', function () {
    // Het verlaagde BTW-tarief in Nederland is 6% (was 9%)
    expect(VatRate::Six->value)->toBe(6);
    expect(VatRate::Six->label())->toBe('6%');
});
