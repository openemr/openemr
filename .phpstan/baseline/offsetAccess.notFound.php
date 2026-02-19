<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Offset \'extension\' might not exist on array\\{dirname\\?\\: string, basename\\: string, extension\\?\\: string, filename\\: string\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'DENEX\'\\|\'DENEXCEP\'\\|\'DENOM\'\\|\'IPP\'\\|\'NUMER\' might not exist on \'00193FC7\\-AEE4\\-4507…\'\\|\'0525FBA2\\-F068\\-4706…\'\\|\'0899A359\\-0CD8\\-4977…\'\\|\'0ED7B212\\-369B\\-489A…\'\\|\'201F5A6E\\-4DDE\\-43A2…\'\\|\'2448B0C6\\-6848\\-4DCB…\'\\|\'26046A5C\\-E2CC\\-4A27…\'\\|\'35B1A6DF\\-1871\\-4633…\'\\|\'3EE6DFF5\\-AB17\\-482F…\'\\|\'3F4CDE57\\-1C5C\\-4250…\'\\|\'40280381\\-3D61\\-56A7…\'\\|\'4327D845\\-6194\\-410D…\'\\|\'4E118B62\\-2AF8\\-4F51…\'\\|\'545DA813\\-89ED\\-4DCD…\'\\|\'663FB12B\\-0FF4\\-49AB…\'\\|\'6721D6DA\\-E87D\\-4E42…\'\\|\'6ED6A787\\-C871\\-49B9…\'\\|\'7549BA9E\\-1841\\-4231…\'\\|\'873AECC7\\-E15B\\-49E7…\'\\|\'9D1135EA\\-BA90\\-45E7…\'\\|\'A72855CE\\-3C60\\-41F9…\'\\|\'B5C9EC50\\-3011\\-43DC…\'\\|\'B61EC2DC\\-0841\\-4906…\'\\|\'C29B6555\\-3BC7\\-416F…\'\\|\'C948D0D2\\-D6E9\\-4099…\'\\|\'D04EFECB\\-A901\\-4565…\'\\|\'E5F80C25\\-6816\\-4992…\'\\|\'EC400908\\-35BE\\-439B…\'\\|\'EDED90E9\\-E4FE\\-47E6…\'\\|\'F48702E6\\-D39A\\-49D8…\'\\|\'FA1B3953\\-AE58\\-4541…\'\\|\'FEC7251A\\-BF8D\\-4472…\'\\|\'FF7016E1\\-E8C7\\-43BA…\'\\|array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\'\\}\\}\\|array\\{IPP\\: \'1C936855\\-E644\\-44C0…\', DENOM\\: \'27F1591C\\-2060\\-462C…\', DENEX\\: \'9B0C3C26\\-D621\\-4EA3…\', NUMER\\: \'3095531C\\-24D7\\-4AFB…\'\\}\\|array\\{IPP\\: \'6E701B1C\\-6CA5\\-4AD5…\', DENOM\\: \'E4DC29B8\\-EB26\\-4A01…\', DENEX\\: \'BB1B4301\\-C275\\-4BAC…\', NUMER\\: \'7669026D\\-3683\\-44CC…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'DENEX\'\\|\'DENEXCEP\'\\|\'DENOM\'\\|\'IPP\'\\|\'NUMER\' might not exist on array\\{\'Numerator 1\'\\: array\\{IPP\\: \'1C936855\\-E644\\-44C0…\', DENOM\\: \'27F1591C\\-2060\\-462C…\', DENEX\\: \'9B0C3C26\\-D621\\-4EA3…\', NUMER\\: \'3095531C\\-24D7\\-4AFB…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'6E701B1C\\-6CA5\\-4AD5…\', DENOM\\: \'E4DC29B8\\-EB26\\-4A01…\', DENEX\\: \'BB1B4301\\-C275\\-4BAC…\', NUMER\\: \'7669026D\\-3683\\-44CC…\'\\}\\}\\|array\\{\'Population Criteria 1\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\'\\}\\}, \'Population Criteria 2\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\}, \'Population Criteria 3\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\}, STRAT1\\: \'40280381\\-3D61\\-56A7…\', STRAT2\\: \'40280381\\-3D61\\-56A7…\'\\}\\|array\\{IPP\\: \'2448B0C6\\-6848\\-4DCB…\', DENOM\\: \'EC400908\\-35BE\\-439B…\', NUMER\\: \'663FB12B\\-0FF4\\-49AB…\', DENEXCEP\\: \'FEC7251A\\-BF8D\\-4472…\'\\}\\|array\\{IPP\\: \'4E118B62\\-2AF8\\-4F51…\', DENOM\\: \'FA1B3953\\-AE58\\-4541…\', NUMER\\: \'35B1A6DF\\-1871\\-4633…\', DENEXCEP\\: \'3EE6DFF5\\-AB17\\-482F…\'\\}\\|array\\{IPP\\: \'6ED6A787\\-C871\\-49B9…\', DENOM\\: \'545DA813\\-89ED\\-4DCD…\', NUMER\\: \'00193FC7\\-AEE4\\-4507…\'\\}\\|array\\{IPP\\: \'873AECC7\\-E15B\\-49E7…\', DENOM\\: \'FF7016E1\\-E8C7\\-43BA…\', NUMER\\: \'201F5A6E\\-4DDE\\-43A2…\'\\}\\|array\\{IPP\\: \'9D1135EA\\-BA90\\-45E7…\', DENOM\\: \'D04EFECB\\-A901\\-4565…\', NUMER\\: \'3F4CDE57\\-1C5C\\-4250…\', DENEX\\: \'0525FBA2\\-F068\\-4706…\'\\}\\|array\\{IPP\\: \'A72855CE\\-3C60\\-41F9…\', DENOM\\: \'26046A5C\\-E2CC\\-4A27…\', NUMER\\: \'0899A359\\-0CD8\\-4977…\', DENEX\\: \'4327D845\\-6194\\-410D…\'\\}\\|array\\{IPP\\: \'C29B6555\\-3BC7\\-416F…\', DENOM\\: \'E5F80C25\\-6816\\-4992…\', NUMER\\: \'C948D0D2\\-D6E9\\-4099…\'\\}\\|array\\{IPP\\: \'EDED90E9\\-E4FE\\-47E6…\', DENOM\\: \'6721D6DA\\-E87D\\-4E42…\', NUMER\\: \'7549BA9E\\-1841\\-4231…\'\\}\\|array\\{IPP\\: \'F48702E6\\-D39A\\-49D8…\', DENOM\\: \'B61EC2DC\\-0841\\-4906…\', NUMER\\: \'0ED7B212\\-369B\\-489A…\', DENEXCEP\\: \'B5C9EC50\\-3011\\-43DC…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'DENEX\'\\|\'DENEXCEP\'\\|\'DENOM\'\\|\'IPP\'\\|\'NUMER\' might not exist on array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\|\'C43CE779\\-C5EE\\-4C15…\'\\|\'FD4649BD\\-B962\\-4CBE…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|non\\-empty\\-string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'DISPLAY_TEXT\' might not exist on array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\|\'C43CE779\\-C5EE\\-4C15…\'\\|\'FD4649BD\\-B962\\-4CBE…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|non\\-empty\\-string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'NUMER\' might not exist on \'00193FC7\\-AEE4\\-4507…\'\\|\'0525FBA2\\-F068\\-4706…\'\\|\'0899A359\\-0CD8\\-4977…\'\\|\'0ED7B212\\-369B\\-489A…\'\\|\'201F5A6E\\-4DDE\\-43A2…\'\\|\'2448B0C6\\-6848\\-4DCB…\'\\|\'26046A5C\\-E2CC\\-4A27…\'\\|\'35B1A6DF\\-1871\\-4633…\'\\|\'3EE6DFF5\\-AB17\\-482F…\'\\|\'3F4CDE57\\-1C5C\\-4250…\'\\|\'40280381\\-3D61\\-56A7…\'\\|\'4327D845\\-6194\\-410D…\'\\|\'4E118B62\\-2AF8\\-4F51…\'\\|\'545DA813\\-89ED\\-4DCD…\'\\|\'663FB12B\\-0FF4\\-49AB…\'\\|\'6721D6DA\\-E87D\\-4E42…\'\\|\'6ED6A787\\-C871\\-49B9…\'\\|\'7549BA9E\\-1841\\-4231…\'\\|\'873AECC7\\-E15B\\-49E7…\'\\|\'9D1135EA\\-BA90\\-45E7…\'\\|\'A72855CE\\-3C60\\-41F9…\'\\|\'B5C9EC50\\-3011\\-43DC…\'\\|\'B61EC2DC\\-0841\\-4906…\'\\|\'C29B6555\\-3BC7\\-416F…\'\\|\'C948D0D2\\-D6E9\\-4099…\'\\|\'D04EFECB\\-A901\\-4565…\'\\|\'E5F80C25\\-6816\\-4992…\'\\|\'EC400908\\-35BE\\-439B…\'\\|\'EDED90E9\\-E4FE\\-47E6…\'\\|\'F48702E6\\-D39A\\-49D8…\'\\|\'FA1B3953\\-AE58\\-4541…\'\\|\'FEC7251A\\-BF8D\\-4472…\'\\|\'FF7016E1\\-E8C7\\-43BA…\'\\|array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\'\\}\\}\\|array\\{IPP\\: \'1C936855\\-E644\\-44C0…\', DENOM\\: \'27F1591C\\-2060\\-462C…\', DENEX\\: \'9B0C3C26\\-D621\\-4EA3…\', NUMER\\: \'3095531C\\-24D7\\-4AFB…\'\\}\\|array\\{IPP\\: \'6E701B1C\\-6CA5\\-4AD5…\', DENOM\\: \'E4DC29B8\\-EB26\\-4A01…\', DENEX\\: \'BB1B4301\\-C275\\-4BAC…\', NUMER\\: \'7669026D\\-3683\\-44CC…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'NUMER\' might not exist on array\\{\'Numerator 1\'\\: array\\{IPP\\: \'1C936855\\-E644\\-44C0…\', DENOM\\: \'27F1591C\\-2060\\-462C…\', DENEX\\: \'9B0C3C26\\-D621\\-4EA3…\', NUMER\\: \'3095531C\\-24D7\\-4AFB…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'6E701B1C\\-6CA5\\-4AD5…\', DENOM\\: \'E4DC29B8\\-EB26\\-4A01…\', DENEX\\: \'BB1B4301\\-C275\\-4BAC…\', NUMER\\: \'7669026D\\-3683\\-44CC…\'\\}\\}\\|array\\{\'Population Criteria 1\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\'\\}\\}, \'Population Criteria 2\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\}, \'Population Criteria 3\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\}, STRAT1\\: \'40280381\\-3D61\\-56A7…\', STRAT2\\: \'40280381\\-3D61\\-56A7…\'\\}\\|array\\{IPP\\: \'2448B0C6\\-6848\\-4DCB…\', DENOM\\: \'EC400908\\-35BE\\-439B…\', NUMER\\: \'663FB12B\\-0FF4\\-49AB…\', DENEXCEP\\: \'FEC7251A\\-BF8D\\-4472…\'\\}\\|array\\{IPP\\: \'4E118B62\\-2AF8\\-4F51…\', DENOM\\: \'FA1B3953\\-AE58\\-4541…\', NUMER\\: \'35B1A6DF\\-1871\\-4633…\', DENEXCEP\\: \'3EE6DFF5\\-AB17\\-482F…\'\\}\\|array\\{IPP\\: \'6ED6A787\\-C871\\-49B9…\', DENOM\\: \'545DA813\\-89ED\\-4DCD…\', NUMER\\: \'00193FC7\\-AEE4\\-4507…\'\\}\\|array\\{IPP\\: \'873AECC7\\-E15B\\-49E7…\', DENOM\\: \'FF7016E1\\-E8C7\\-43BA…\', NUMER\\: \'201F5A6E\\-4DDE\\-43A2…\'\\}\\|array\\{IPP\\: \'9D1135EA\\-BA90\\-45E7…\', DENOM\\: \'D04EFECB\\-A901\\-4565…\', NUMER\\: \'3F4CDE57\\-1C5C\\-4250…\', DENEX\\: \'0525FBA2\\-F068\\-4706…\'\\}\\|array\\{IPP\\: \'A72855CE\\-3C60\\-41F9…\', DENOM\\: \'26046A5C\\-E2CC\\-4A27…\', NUMER\\: \'0899A359\\-0CD8\\-4977…\', DENEX\\: \'4327D845\\-6194\\-410D…\'\\}\\|array\\{IPP\\: \'C29B6555\\-3BC7\\-416F…\', DENOM\\: \'E5F80C25\\-6816\\-4992…\', NUMER\\: \'C948D0D2\\-D6E9\\-4099…\'\\}\\|array\\{IPP\\: \'EDED90E9\\-E4FE\\-47E6…\', DENOM\\: \'6721D6DA\\-E87D\\-4E42…\', NUMER\\: \'7549BA9E\\-1841\\-4231…\'\\}\\|array\\{IPP\\: \'F48702E6\\-D39A\\-49D8…\', DENOM\\: \'B61EC2DC\\-0841\\-4906…\', NUMER\\: \'0ED7B212\\-369B\\-489A…\', DENEXCEP\\: \'B5C9EC50\\-3011\\-43DC…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'NUMER\' might not exist on array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\|\'C43CE779\\-C5EE\\-4C15…\'\\|\'FD4649BD\\-B962\\-4CBE…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}\\|array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\|non\\-empty\\-string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'STRAT1\'\\|\'STRAT2\' might not exist on array\\{\'Numerator 1\'\\: array\\{IPP\\: \'1C936855\\-E644\\-44C0…\', DENOM\\: \'27F1591C\\-2060\\-462C…\', DENEX\\: \'9B0C3C26\\-D621\\-4EA3…\', NUMER\\: \'3095531C\\-24D7\\-4AFB…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'6E701B1C\\-6CA5\\-4AD5…\', DENOM\\: \'E4DC29B8\\-EB26\\-4A01…\', DENEX\\: \'BB1B4301\\-C275\\-4BAC…\', NUMER\\: \'7669026D\\-3683\\-44CC…\'\\}\\}\\|array\\{\'Population Criteria 1\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\'\\}\\}, \'Population Criteria 2\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS1\\:…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'BMI Recorded, RS2\\:…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}\\}, \'Population Criteria 3\'\\: array\\{\'Numerator 1\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'FD4649BD\\-B962\\-4CBE…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Nutrition…\'\\}, \'Numerator 2\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'52FBD726\\-4DD1\\-48F5…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}, \'Numerator 3\'\\: array\\{IPP\\: \'10127790\\-AE94\\-4070…\', DENOM\\: \'3B3C1568\\-F875\\-49B1…\', DENEX\\: \'B288D5A4\\-D573\\-4FAA…\', NUMER\\: \'C43CE779\\-C5EE\\-4C15…\', STRAT\\: \'40280381\\-3D61\\-56A7…\', DISPLAY_TEXT\\: \'Physical Activity…\'\\}\\}, STRAT1\\: \'40280381\\-3D61\\-56A7…\', STRAT2\\: \'40280381\\-3D61\\-56A7…\'\\}\\|array\\{IPP\\: \'2448B0C6\\-6848\\-4DCB…\', DENOM\\: \'EC400908\\-35BE\\-439B…\', NUMER\\: \'663FB12B\\-0FF4\\-49AB…\', DENEXCEP\\: \'FEC7251A\\-BF8D\\-4472…\'\\}\\|array\\{IPP\\: \'4E118B62\\-2AF8\\-4F51…\', DENOM\\: \'FA1B3953\\-AE58\\-4541…\', NUMER\\: \'35B1A6DF\\-1871\\-4633…\', DENEXCEP\\: \'3EE6DFF5\\-AB17\\-482F…\'\\}\\|array\\{IPP\\: \'6ED6A787\\-C871\\-49B9…\', DENOM\\: \'545DA813\\-89ED\\-4DCD…\', NUMER\\: \'00193FC7\\-AEE4\\-4507…\'\\}\\|array\\{IPP\\: \'873AECC7\\-E15B\\-49E7…\', DENOM\\: \'FF7016E1\\-E8C7\\-43BA…\', NUMER\\: \'201F5A6E\\-4DDE\\-43A2…\'\\}\\|array\\{IPP\\: \'9D1135EA\\-BA90\\-45E7…\', DENOM\\: \'D04EFECB\\-A901\\-4565…\', NUMER\\: \'3F4CDE57\\-1C5C\\-4250…\', DENEX\\: \'0525FBA2\\-F068\\-4706…\'\\}\\|array\\{IPP\\: \'A72855CE\\-3C60\\-41F9…\', DENOM\\: \'26046A5C\\-E2CC\\-4A27…\', NUMER\\: \'0899A359\\-0CD8\\-4977…\', DENEX\\: \'4327D845\\-6194\\-410D…\'\\}\\|array\\{IPP\\: \'C29B6555\\-3BC7\\-416F…\', DENOM\\: \'E5F80C25\\-6816\\-4992…\', NUMER\\: \'C948D0D2\\-D6E9\\-4099…\'\\}\\|array\\{IPP\\: \'EDED90E9\\-E4FE\\-47E6…\', DENOM\\: \'6721D6DA\\-E87D\\-4E42…\', NUMER\\: \'7549BA9E\\-1841\\-4231…\'\\}\\|array\\{IPP\\: \'F48702E6\\-D39A\\-49D8…\', DENOM\\: \'B61EC2DC\\-0841\\-4906…\', NUMER\\: \'0ED7B212\\-369B\\-489A…\', DENEXCEP\\: \'B5C9EC50\\-3011\\-43DC…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'measure\' does not exist on array\\{name\\: \'Blood Pressure…\', category\\: \'Blood Pressure\', unit\\: \'mmHg\', code\\: \'8462\\-4\'\\}\\|array\\{name\\: \'Blood Pressure…\', category\\: \'Blood Pressure\', unit\\: \'mmHg\', code\\: \'8480\\-6\'\\}\\|array\\{name\\: \'Body Mass Index\', category\\: \'Body Mass Index\', unit\\: \'kg/m2\', code\\: \'39156\\-5\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/qrda_category1_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 7 does not exist on array\\{0\\: array\\{\\}, 2\\: mixed, 4\\: mixed, 8\\: non\\-falsy\\-string, 10\\: non\\-falsy\\-string, 3\\: non\\-falsy\\-string, 6\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 might not exist on array\\{0\\: string, 1\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/eRxPage.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ODDISC\'\\|\'OSDISC\' does not exist on string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ODMACULA\'\\|\'OSMACULA\' does not exist on string\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ODPERIPH\'\\|\'OSPERIPH\' does not exist on string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ODVESSELS\'\\|\'OSVESSELS\' does not exist on string\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resnote\' might not exist on array\\{\\}\\|array\\{resnote\\: string, restype\\: string, resdate\\: string, reslist\\?\\: string, display\\: string, short_title\\: string\\}\\|array\\{resnote\\: string, restype\\: string, resdate\\: string, reslist\\?\\: string\\}\\|array\\{resnote\\: string\\}\\|array\\{title\\: string, status\\: mixed, begdate\\: mixed, enddate\\: mixed, returndate\\: mixed, occurrence\\: mixed, classification\\: mixed, referredby\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'restype\' might not exist on array\\{resnote\\: string, restype\\: string, resdate\\: string, reslist\\?\\: string, display\\: string, short_title\\: string\\}\\|array\\{resnote\\: string, restype\\: string, resdate\\: string, reslist\\?\\: string\\}\\|array\\{resnote\\: string\\}\\|array\\{title\\: string, status\\: mixed, begdate\\: mixed, enddate\\: mixed, returndate\\: mixed, occurrence\\: mixed, classification\\: mixed, referredby\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'form_id\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'form_id\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/group_attendance/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'procedure_name\' might not exist on array\\<string, mixed\\>\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/procedure_order/templates/procedure_specimen_row.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'fname\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lname\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/requisition/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'DOB\' might not exist on \'\'\\|array\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'sex\' might not exist on \'\'\\|array\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/vitals/growthchart/chart.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'file\' might not exist on array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'version\' does not exist on array\\{\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'facility\' does not exist on array\\{result_data_type\\: string, comments\\: "\\\\n", document_id\\?\\: mixed, date\\: mixed, result_code\\: mixed, result_text\\: mixed, units\\: mixed, range\\: mixed, \\.\\.\\.\\}\\|array\\{result_data_type\\: string, comments\\: non\\-falsy\\-string, result\\: mixed, result_code\\: mixed, result_text\\: mixed, date\\: mixed, units\\: mixed, range\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExportJobTaskService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on string\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/FaxDocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\{\\}\\|array\\{1\\?\\: non\\-falsy\\-string, 2\\?\\: non\\-falsy\\-string, 0\\?\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 might not exist on array\\{\\}\\|array\\{1\\?\\: non\\-falsy\\-string, 2\\?\\: non\\-falsy\\-string, 0\\?\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 2 might not exist on array\\{\\}\\|array\\{1\\?\\: non\\-falsy\\-string, 2\\?\\: non\\-falsy\\-string, 0\\?\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'email\' might not exist on array\\|string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'email\' might not exist on array\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'height\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/indexrx.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'care_plan\' might not exist on array\\{\\}\\|array\\{care_plan\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, text\\: mixed, code\\: mixed, description\\: mixed, plan_type\\: mixed\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'dirname\' might not exist on array\\{dirname\\?\\: string, basename\\: string, extension\\?\\: string, filename\\: string\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'encounter\' might not exist on array\\{\\}\\|array\\{encounter\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, date\\: mixed, provider_npi\\: mixed, provider_name\\: mixed, provider_address\\: mixed, provider_city\\: mixed, provider_state\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'functional_cognitive_status\' might not exist on array\\{\\}\\|array\\{functional_cognitive_status\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, text\\: mixed, code\\: mixed, date\\: mixed, description\\: mixed\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'immunization\' might not exist on array\\{\\}\\|array\\{immunization\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, administered_date\\: mixed, route_code\\: mixed, route_code_text\\: mixed, cvx_code\\: mixed, cvx_code_text\\: mixed, amount_administered\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lists1\' might not exist on array\\{\\}\\|array\\{lists1\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, begdate\\: mixed, enddate\\: mixed, list_code\\: mixed, list_code_text\\: mixed, status\\: mixed, observation_text\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lists2\' might not exist on array\\{\\}\\|array\\{lists2\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, begdate\\: mixed, enddate\\: mixed, list_code\\: mixed, list_code_text\\: mixed, severity_al\\: mixed, status\\: mixed, reaction\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lists3\' might not exist on array\\{\\}\\|array\\{lists3\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, begdate\\: mixed, enddate\\: mixed, route\\: mixed, note\\: mixed, indication\\: mixed, route_display\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'procedure\' might not exist on array\\{\\}\\|array\\{procedure\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, root\\: mixed, codeSystemName\\: mixed, code\\: mixed, code_text\\: mixed, date\\: mixed, represented_organization1\\: mixed, represented_organization_address1\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'procedure_result\' might not exist on array\\{\\}\\|array\\{procedure_result\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{proc_text\\: mixed, proc_code\\: mixed, extension\\: mixed, date\\: mixed, status\\: mixed, results_text\\: mixed, results_code\\: mixed, results_range\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'reaction_text\' does not exist on array\\{provider_name\\: mixed, provider_family\\: mixed, provider_address\\: mixed, provider_city\\: mixed, provider_state\\: mixed, provider_postalCode\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'referral\' might not exist on array\\{\\}\\|array\\{referral\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{body\\: mixed, root\\: mixed\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'vitals\' might not exist on array\\{\\}\\|array\\{vitals\\?\\: non\\-empty\\-array\\<int\\<1, max\\>, array\\{extension\\: mixed, date\\: mixed, temperature\\: mixed, bpd\\: mixed, bps\\: mixed, head_circ\\: mixed, pulse\\: mixed, height\\: mixed, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\{\\}\\|array\\{0\\?\\: non\\-empty\\-array\\<mixed\\>\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'field_name_value_array\' might not exist on array\\{approval_status\\: 1, type\\: 11, ip_address\\: mixed, field_name_value_array\\?\\: non\\-empty\\-array\\<non\\-empty\\-array\\<array\\<mixed\\>\\>\\>, entry_identification_array\\?\\: non\\-empty\\-array\\<non\\-empty\\-array\\<mixed\\>\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'documents\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'immunizations\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lists1\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lists2\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'patient_data\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'prescriptions\' might not exist on array\\{\\}\\|array\\{documents\\?\\: non\\-empty\\-array, misc_address_book\\?\\: non\\-empty\\-array, procedure_type\\?\\: non\\-empty\\-array, procedure_result\\?\\: non\\-empty\\-array, immunizations\\?\\: non\\-empty\\-array, prescriptions\\?\\: non\\-empty\\-array, lists2\\?\\: non\\-empty\\-array, lists1\\?\\: non\\-empty\\-array, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'facility\' does not exist on array\\{result_data_type\\: string, comments\\: "\\\\n", document_id\\?\\: mixed, date\\: mixed, result_code\\: mixed, result_text\\: mixed, units\\: mixed, range\\: mixed, \\.\\.\\.\\}\\|array\\{result_data_type\\: string, comments\\: non\\-falsy\\-string, result\\: mixed, result_code\\: mixed, result_text\\: mixed, date\\: mixed, units\\: mixed, range\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'date\' does not exist on non\\-empty\\-list\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'extension\' might not exist on array\\{dirname\\?\\: string, basename\\: string, extension\\?\\: string, filename\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'code\' might not exist on array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: float, payments\\: 0\\}\\|array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, code\\: mixed, code_type\\: mixed, charges\\: 0\\|float, payments\\: 0\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'code_type\' might not exist on array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: float, payments\\: 0\\}\\|array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, code\\: mixed, code_type\\: mixed, charges\\: 0\\|float, payments\\: 0\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/front_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset float does not exist on list\\<non\\-empty\\-array\\>\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset float does not exist on list\\<non\\-empty\\-array\\>\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 might not exist on array\\{0\\: \'D\', 1\\?\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/gen_hl7_order.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'groupnumber\' might not exist on array\\{id\\: mixed, pid\\: mixed, encounter\\: mixed, invnumber\\: non\\-falsy\\-string, custid\\: mixed, name\\: non\\-falsy\\-string, address1\\: mixed, city\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'policy\' might not exist on array\\{id\\: mixed, pid\\: mixed, encounter\\: mixed, invnumber\\: non\\-falsy\\-string, custid\\: mixed, name\\: non\\-falsy\\-string, address1\\: mixed, city\\: mixed, \\.\\.\\.\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/collections_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'patients\' might not exist on array\\{visits\\: int\\<1, max\\>, charges\\: float, patients\\?\\: int\\<1, max\\>\\}\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/reports/insurance_allocation_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset int\\<0, 11\\> does not exist on array\\{\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/ippf_daily.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ALL\' might not exist on array\\{\\}\\|array\\{ALL\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'Fac\' might not exist on array\\{\\}\\|array\\{Stat\\?\\: non\\-falsy\\-string, Fac\\?\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'Stat\' might not exist on array\\{\\}\\|array\\{Stat\\: non\\-falsy\\-string\\}\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'memo\' might not exist on array\\{amount\\: \\(array\\|float\\|int\\), date\\: mixed, memo\\: non\\-falsy\\-string, payeeid\\: mixed, name\\: mixed\\}\\|array\\{amount\\: \\(array\\|float\\|int\\), date\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/OFX.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'excluded\' might not exist on array\\{pass_target\\: \\(array\\|float\\|int\\), percentage\\: string, total_patients\\: \\(array\\|float\\|int\\)\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\), pass_filter\\: \\(array\\|float\\|int\\), pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'pass_filter\' might not exist on array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\), pass_filter\\?\\: \\(array\\|float\\|int\\), pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'pass_target\' might not exist on array\\{pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\), pass_filter\\: \\(array\\|float\\|int\\), pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\), pass_filter\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_patients\' might not exist on array\\{pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\|array\\{total_patients\\: \\(array\\|float\\|int\\), excluded\\: \\(array\\|float\\|int\\), pass_filter\\: \\(array\\|float\\|int\\), pass_target\\: \\(array\\|float\\|int\\), percentage\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'desc\' might not exist on array\\{name\\?\\: string\\|false, mime\\: non\\-empty\\-list\\<string\\>, desc\\?\\: string\\|false, file\\: string, doc_id\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'desc\' might not exist on array\\{name\\?\\: string\\|false, mime\\: non\\-empty\\-list\\<string\\>, desc\\?\\: string\\|false, file\\: string, doc_id\\?\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'host\' might not exist on array\\{scheme\\: \'http\'\\|\'tcp\', host\\?\\: string, port\\?\\: int\\<0, 65535\\>, user\\?\\: string, pass\\?\\: string, path\\?\\: string, query\\?\\: string, fragment\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'host\' might not exist on array\\{scheme\\: \'https\', host\\?\\: string, port\\?\\: int\\<0, 65535\\>, user\\?\\: string, pass\\?\\: string, path\\?\\: string, query\\?\\: string, fragment\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'mime\' might not exist on array\\{name\\?\\: string\\|false, mime\\?\\: non\\-empty\\-list\\<string\\>\\|string\\|false, desc\\?\\: string\\|false, file\\: string, doc_id\\?\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'name\' might not exist on array\\{name\\?\\: string\\|false, mime\\?\\: non\\-empty\\-list\\<string\\>\\|string\\|false, desc\\?\\: string\\|false, file\\: string, doc_id\\?\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'port\' might not exist on array\\{scheme\\: \'http\'\\|\'https\'\\|\'ssl\'\\|\'sslv3\'\\|\'tcp\'\\|\'tls\', host\\?\\: string, port\\?\\: int\\<0, 65535\\>, user\\?\\: string, pass\\?\\: string, path\\?\\: string, query\\?\\: string, fragment\\?\\: string\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'port\' might not exist on array\\{scheme\\: \'https\', host\\?\\: string, port\\?\\: int\\<0, 65535\\>, user\\?\\: string, pass\\?\\: string, path\\?\\: string, query\\?\\: string, fragment\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/direct_message_check.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'svcpmt\' might not exist on array\\{pmt\\: 0\\|\'\'\\|float, fee\\: 0\\|float, clmpmt\\: 0\\|float, clmadj\\: 0\\|float, ptrsp\\: 0\\|float, svcptrsp\\: 0\\|float, svcfee\\: float\\|int, svcpmt\\?\\: \\(float\\|int\\), \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'GS\' might not exist on array\\{ISA\\: non\\-empty\\-array\\<array\\{count\\: int\\<1, max\\>, gscount\\: string\\}\\|array\\{start\\: numeric\\-string, sender\\: string, receiver\\: string, icn\\: string, date\\: string, version\\: string, count\\: int\\<1, max\\>, gscount\\: string\\}\\>, GS\\?\\: non\\-empty\\-array\\<non\\-empty\\-array\\<\'count\'\\|\'date\'\\|\'gsn\'\\|\'icn\'\\|\'sender\'\\|\'srcid\'\\|\'start\'\\|\'stcount\'\\|\'type\', mixed\\>\\>, ST\\?\\: non\\-empty\\-array\\<int\\<0, max\\>, non\\-empty\\-array\\{start\\?\\: numeric\\-string, count\\?\\: string, stn\\?\\: string, gsn\\?\\: mixed, icn\\?\\: mixed, type\\?\\: string, trace\\?\\: string, acct\\?\\: list\\<string\\>, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ST\' might not exist on array\\{ISA\\?\\: non\\-empty\\-array\\<array\\{start\\?\\: numeric\\-string, sender\\?\\: string, receiver\\?\\: string, icn\\?\\: string, date\\?\\: string, version\\?\\: string, count\\?\\: int\\<1, max\\>, gscount\\?\\: string\\}\\>, GS\\: non\\-empty\\-array\\<non\\-empty\\-array\\<\'count\'\\|\'date\'\\|\'gsn\'\\|\'icn\'\\|\'sender\'\\|\'srcid\'\\|\'start\'\\|\'stcount\'\\|\'type\', mixed\\>\\>, ST\\?\\: non\\-empty\\-array\\<int\\<0, max\\>, non\\-empty\\-array\\{start\\?\\: numeric\\-string, count\\?\\: string, stn\\?\\: string, gsn\\?\\: mixed, icn\\?\\: mixed, type\\?\\: string, trace\\?\\: string, acct\\?\\: list\\<string\\>, \\.\\.\\.\\}\\>\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'count\' might not exist on array\\{start\\: mixed, count\\?\\: \\(float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'icn\' might not exist on array\\{count\\: int\\<1, max\\>, gscount\\: string\\}\\|array\\{start\\: numeric\\-string, sender\\: string, receiver\\: string, icn\\: string, date\\: string, version\\: string, count\\: int\\<1, max\\>, gscount\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 might not exist on \'\'\\|non\\-empty\\-list\\<string\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_835_accounting.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\{0\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'number_inactivated_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\), number_new_reminders\\: \\(array\\|float\\|int\\), number_updated_reminders\\: \\(array\\|float\\|int\\), number_unchanged_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'number_new_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'number_unchanged_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\), number_new_reminders\\: \\(array\\|float\\|int\\), number_updated_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'number_updated_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\), number_new_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_active_actions\' does not exist on array\\{\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_post_active_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\), number_new_reminders\\: \\(array\\|float\\|int\\), number_updated_reminders\\: \\(array\\|float\\|int\\), number_unchanged_reminders\\: \\(array\\|float\\|int\\), number_inactivated_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_post_unsent_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\), total_pre_unsent_reminders\\: \\(array\\|float\\|int\\), number_new_reminders\\: \\(array\\|float\\|int\\), number_updated_reminders\\: \\(array\\|float\\|int\\), number_unchanged_reminders\\: \\(array\\|float\\|int\\), number_inactivated_reminders\\: \\(array\\|float\\|int\\), total_post_active_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_pre_active_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'total_pre_unsent_reminders\' does not exist on array\\{total_active_actions\\: \\(array\\|float\\|int\\), total_pre_active_reminders\\: \\(array\\|float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/reminders.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'insert_tags\' does not exist on array\\{\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'timestamp\' does not exist on array\\{\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resource_timestamp\' does not exist on array\\{get_source\\: false, quiet\\: true, resource_name\\: int\\|string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resource_timestamp\' does not exist on array\\{resource_base_path\\: mixed, get_source\\: false, quiet\\: true, resource_name\\: int\\|string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resource_timestamp\' does not exist on array\\{resource_name\\: mixed, resource_base_path\\: mixed, 0\\: true\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resource_timestamp\' does not exist on array\\{resource_name\\: mixed, resource_base_path\\: mixed, get_source\\: false\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'resource_type\' does not exist on array\\{resource_name\\: mixed, resource_base_path\\: mixed, get_source\\: false\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'source_content\' does not exist on array\\{resource_name\\: mixed, resource_base_path\\: mixed, 0\\: true\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.config_load.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'args\' might not exist on array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'file\' might not exist on array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'line\' might not exist on array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'FLD1\'\\|\'FLD2\'\\|\'FLD3\'\\|\'FLD4\' might not exist on array\\{TABLENAME\\: \'icd10_dx_order_code\', FLD1\\: \'dx_code\', POS1\\: 6, LEN1\\: 7, FLD2\\: \'valid_for_coding\', POS2\\: 14, LEN2\\: 1, FLD3\\: \'short_desc\', \\.\\.\\.\\}\\|array\\{TABLENAME\\: \'icd10_pcs_order_code\', FLD1\\: \'pcs_code\', POS1\\: 0, LEN1\\: 7, FLD2\\: \'long_desc\', POS2\\: 8, LEN2\\: 300, REV\\: \\(float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'LEN1\'\\|\'LEN2\'\\|\'LEN3\'\\|\'LEN4\' might not exist on array\\{TABLENAME\\: \'icd10_dx_order_code\', FLD1\\: \'dx_code\', POS1\\: 6, LEN1\\: 7, FLD2\\: \'valid_for_coding\', POS2\\: 14, LEN2\\: 1, FLD3\\: \'short_desc\', \\.\\.\\.\\}\\|array\\{TABLENAME\\: \'icd10_pcs_order_code\', FLD1\\: \'pcs_code\', POS1\\: 0, LEN1\\: 7, FLD2\\: \'long_desc\', POS2\\: 8, LEN2\\: 300, REV\\: \\(float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'POS1\'\\|\'POS2\'\\|\'POS3\'\\|\'POS4\' might not exist on array\\{TABLENAME\\: \'icd10_dx_order_code\', FLD1\\: \'dx_code\', POS1\\: 6, LEN1\\: 7, FLD2\\: \'valid_for_coding\', POS2\\: 14, LEN2\\: 1, FLD3\\: \'short_desc\', \\.\\.\\.\\}\\|array\\{TABLENAME\\: \'icd10_pcs_order_code\', FLD1\\: \'pcs_code\', POS1\\: 0, LEN1\\: 7, FLD2\\: \'long_desc\', POS2\\: 8, LEN2\\: 300, REV\\: \\(float\\|int\\)\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\{0\\?\\: string\\}\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/lib/appsql.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'extension\' might not exist on array\\{dirname\\?\\: string, basename\\: string, extension\\?\\: string, filename\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/thumbnail.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'extension\' might not exist on array\\{dirname\\?\\: string, basename\\: string, extension\\?\\: string, filename\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/HTTP/RequestUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'class\' might not exist on array\\{function\\: string, line\\?\\: int, file\\?\\: string, class\\?\\: class\\-string, type\\?\\: \'\\-\\>\'\\|\'\\:\\:\', args\\?\\: list\\<mixed\\>, object\\?\\: object\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'code\' might not exist on array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: 0\\|float, payments\\: 0, reason\\: mixed, code_type\\: mixed, code\\: mixed\\}\\|array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: float, payments\\: 0\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'code_type\' might not exist on array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: 0\\|float, payments\\: 0, reason\\: mixed, code_type\\: mixed, code\\: mixed\\}\\|array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: float, payments\\: 0\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'reason\' might not exist on array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: 0\\|float, payments\\: 0, reason\\: mixed, code_type\\: mixed, code\\: mixed\\}\\|array\\{encounter\\: mixed, date\\: mixed, last_level_closed\\: mixed, charges\\: float, payments\\: 0\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'data\' might not exist on array\\{\\}\\|array\\{data\\: non\\-empty\\-array, company\\: array\\|false, object\\: InsuranceCompany\\}\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Billing/Claim.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'context\' might not exist on array\\{id\\: int, parent\\: int, error\\: int, start_date\\?\\: string, end_date\\?\\: string, context\\?\\: literal\\-string&non\\-falsy\\-string, 1\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'context\' might not exist on array\\{id\\: int\\<min, 0\\>\\|int\\<3, max\\>, parent\\: int, error\\: int, start_date\\?\\: string, end_date\\?\\: string, context\\?\\: literal\\-string&non\\-falsy\\-string, 1\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'end_date\' might not exist on array\\{id\\: int, parent\\: int, error\\: int, start_date\\?\\: string, end_date\\?\\: string, context\\: \'EB\', 1\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'message\' might not exist on array\\{type\\?\\: string, benefit_type\\?\\: mixed, start_date\\: mixed, end_date\\: mixed, coverage_level\\?\\: mixed, coverage_type\\?\\: mixed, plan_type\\?\\: mixed, plan_description\\?\\: string, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'start_date\' might not exist on array\\{id\\: int, parent\\: int, error\\: int, start_date\\?\\: string, end_date\\?\\: string, context\\: \'EB\', 1\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/EDI270.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'bal\' might not exist on array\\{chg\\: float\\|int, bal\\: float\\|int, code_type\\: mixed, code_value\\: mixed, modifier\\: mixed, code_text\\: mixed, dtl\\?\\: non\\-empty\\-array\\<\'          1000\'\\|\'          1001\', array\\{chg\\: numeric\\-string\\}\\>\\}\\|array\\{chg\\: float\\|int, bal\\?\\: float\\|int, dtl\\?\\: non\\-empty\\-array\\<\'          1000\'\\|\'          1001\', array\\{chg\\: numeric\\-string\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/InvoiceSummary.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'dtl\' might not exist on array\\{chg\\: float\\|int, bal\\: float\\|int, code_type\\: mixed, code_value\\: mixed, modifier\\: mixed, code_text\\: mixed, dtl\\?\\: non\\-empty\\-array\\<\'          1000\'\\|\'          1001\', array\\{chg\\: numeric\\-string\\}\\>\\}\\|array\\{chg\\: float\\|int, bal\\: float\\|int, dtl\\?\\: non\\-empty\\-array\\<\'          1000\'\\|\'          1001\', array\\{chg\\: numeric\\-string\\}\\>\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/InvoiceSummary.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'table_name\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ClientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'actions\' might not exist on array\\{hasRestrictions\\: true, restrictions\\?\\: non\\-empty\\-array\\<array\\{label\\: \'Clinical Test\'\\|\'Encounter Diagnoses\'\\|\'Health Concerns\'\\|\'Laboratory\'\\|\'Problem List Items\'\\|\'Social Determinants…\'\\|\'Social History\'\\|\'Survey\'\\|\'Vital Signs\', value\\: \'http\\://hl7\\.org/fhir…\'\\|\'http\\://terminology…\', selected\\: true, actions\\: array\\{0\\?\\: \'c\'\\|\'d\'\\|\'r\'\\|\'s\'\\|\'u\', 1\\?\\: \'d\'\\|\'r\'\\|\'s\'\\|\'u\', 2\\?\\: \'d\'\\|\'s\'\\|\'u\', 3\\?\\: \'d\'\\|\'s\', 4\\?\\: \'s\'\\}\\}\\>\\}\\|array\\{name\\: mixed, description\\: string, context\\: mixed, version\\: mixed, actions\\: array\\{c\\: array\\{enabled\\: false\\}\\|array\\{enabled\\: true\\}, r\\: array\\{enabled\\: false\\}\\|array\\{enabled\\: true\\}, u\\: array\\{enabled\\: false\\}\\|array\\{enabled\\: true\\}, d\\: array\\{enabled\\: false\\}\\|array\\{enabled\\: true\\}, s\\: array\\{enabled\\: false\\}\\|array\\{enabled\\: true\\}\\}, restrictions\\: array\\<array\\{label\\: mixed, value\\: mixed, selected\\: true, actions\\: mixed\\}\\>, hasRestrictions\\: true, isUnrestricted\\: bool, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/ScopePermissionParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 might not exist on array\\{0\\?\\: string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/InsuranceCompanyService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'display\' might not exist on array\\{\\}\\|array\\{type\\: \'boolean\'\\|\'date\'\\|\'datetime\'\\|\'decimal\'\\|\'integer\'\\|\'quantity\'\\|\'string\'\\|\'time\'\\|\'uri\', display\\: mixed\\}\\|array\\{type\\: \'coding\', system\\: mixed, code\\: mixed, display\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset mixed does not exist on array\\{\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'display\' might not exist on array\\{\\}\\|array\\{type\\: \'boolean\'\\|\'date\'\\|\'datetime\'\\|\'decimal\'\\|\'integer\'\\|\'quantity\'\\|\'string\'\\|\'time\'\\|\'uri\', display\\: mixed\\}\\|array\\{type\\: \'coding\', system\\: mixed, code\\: mixed, display\\: mixed\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 4 does not exist on array\\{non\\-falsy\\-string, non\\-empty\\-string, non\\-empty\\-string, non\\-empty\\-string\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'type\' does not exist on non\\-empty\\-list\\<array\\<mixed\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/CoverageValidator.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'secondaryAddress\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/USPS/USPSAddressVerifyV3Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'streetAddress\' might not exist on array\\|null\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/USPS/USPSAddressVerifyV3Test.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'PATH_INFO\' does not exist on array\\{REQUEST_METHOD\\: \'GET\', HTTP_HOST\\: \'localhost\', DOCUMENT_ROOT\\: \'/var/www/html\', REQUEST_URI\\: \'/apis/dispatch\\.php\\?…\', QUERY_STRING\\: \'_REWRITE_COMMAND\\=…\', SCRIPT_NAME\\: \'/apis/dispatch\\.php\', SCRIPT_FILENAME\\: \'/var/www/html…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'PATH_INFO\' does not exist on array\\{REQUEST_METHOD\\: \'GET\', HTTP_HOST\\: \'localhost\', DOCUMENT_ROOT\\: \'/var/www/html\', REQUEST_URI\\: \'/dispatch\\.php\\?…\', QUERY_STRING\\: \'_REWRITE_COMMAND\\=…\', SCRIPT_NAME\\: \'/dispatch\\.php\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'PATH_INFO\' does not exist on array\\{REQUEST_METHOD\\: \'GET\', HTTP_HOST\\: \'localhost\', DOCUMENT_ROOT\\: \'/var/www/html…\', REQUEST_URI\\: \'/apis/dispatch\\.php\\?…\', QUERY_STRING\\: \'_REWRITE_COMMAND…\', SCRIPT_NAME\\: \'/apis/dispatch\\.php\', SCRIPT_FILENAME\\: \'/var/www/html…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'PATH_INFO\' does not exist on array\\{REQUEST_METHOD\\: \'POST\', HTTP_HOST\\: \'localhost\', DOCUMENT_ROOT\\: \'/var/www/html…\', REQUEST_URI\\: \'/apis/dispatch\\.php\\?…\', QUERY_STRING\\: \'_REWRITE_COMMAND…\', SCRIPT_NAME\\: \'/apis/dispatch\\.php\', SCRIPT_FILENAME\\: \'/var/www/html…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'PHP_SELF\' does not exist on array\\{REQUEST_METHOD\\: \'GET\', HTTP_HOST\\: \'localhost\', DOCUMENT_ROOT\\: \'/var/www/html…\', REQUEST_URI\\: \'/apis/dispatch\\.php\\?…\', QUERY_STRING\\: \'_REWRITE_COMMAND…\', SCRIPT_NAME\\: \'/apis/dispatch\\.php\', SCRIPT_FILENAME\\: \'/var/www/html…\'\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Http/HttpRestRequestTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
