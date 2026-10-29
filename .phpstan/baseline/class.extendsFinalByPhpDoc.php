<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedList extends @final class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedListFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedList extends @final class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedListFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedList extends @final class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedListFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
];
$ignoreErrors[] = [
    'message' => '#^Anonymous class extends @final class OpenEMR\\\\Core\\\\OEGlobalsBag\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Unit/Services/OpenEMRGlobalsBagAdapterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedList extends @final class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedListFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
];
$ignoreErrors[] = [
    'message' => '#^Class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedList extends @final class DeepCopy\\\\TypeFilter\\\\Spl\\\\SplDoublyLinkedListFilter\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
